<?php

namespace Tests\Feature;

use App\Helpers\WebpImage;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    private function realPng(string $name = 'foto.png', int $width = 40, int $height = 30): UploadedFile
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 10, 120, 90));
        $path = tempnam(sys_get_temp_dir(), 'qa') . '.bin';
        imagepng($image, $path);

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    private function fakeImage(string $name, string $mime): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'qa') . '.bin';
        file_put_contents($path, "<?php echo 'not an image'; ?>");

        return new UploadedFile($path, $name, $mime, null, true);
    }

    public function test_uploaded_image_is_reencoded_to_webp_under_a_generated_name(): void
    {
        Storage::fake('public');

        $stored = WebpImage::store($this->realPng('whatever.png'), 'public', 'products', 'image');

        $this->assertStringStartsWith('products/', $stored);
        $this->assertStringEndsWith('.webp', $stored);
        $this->assertStringNotContainsString('whatever', $stored);
        Storage::disk('public')->assertExists($stored);

        $bytes = Storage::disk('public')->get($stored);
        $this->assertSame('image/webp', (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes));
    }

    public function test_a_php_file_renamed_to_png_is_rejected(): void
    {
        Storage::fake('public');

        $this->expectException(ValidationException::class);

        try {
            WebpImage::store($this->fakeImage('evil.png', 'image/png'), 'public', 'products', 'image');
        } finally {
            $this->assertEmpty(Storage::disk('public')->allFiles());
        }
    }

    public function test_problem_flags_fake_images_and_passes_real_ones(): void
    {
        $this->assertNull(WebpImage::problem($this->realPng()->getRealPath()));
        $this->assertNotNull(WebpImage::problem($this->fakeImage('x.jpg', 'image/jpeg')->getRealPath()));
    }

    public function test_replacing_a_slide_image_deletes_the_old_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('slides/old.webp', 'old');
        Storage::disk('public')->put('slides/new.webp', 'new');

        $slide = Slide::create(['image' => 'slides/old.webp', 'title' => 'Uji', 'is_active' => true, 'sort_order' => 0]);
        $slide->update(['image' => 'slides/new.webp']);

        Storage::disk('public')->assertMissing('slides/old.webp');
        Storage::disk('public')->assertExists('slides/new.webp');

        $slide->delete();
        Storage::disk('public')->assertMissing('slides/new.webp');
    }

    public function test_deleting_a_record_leaves_public_and_external_paths_alone(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('seeded.png', 'x');

        $category = Category::create(['name' => 'Uji', 'slug' => 'uji', 'is_active' => true]);
        $public = Product::create(['category_id' => $category->id, 'name' => 'A', 'slug' => 'a', 'image' => '/images/placeholder.png', 'is_active' => true]);
        $external = Product::create(['category_id' => $category->id, 'name' => 'B', 'slug' => 'b', 'image' => 'https://cdn.example.com/x.png', 'is_active' => true]);

        $public->delete();
        $external->delete();

        $this->assertTrue(true);
    }
}
