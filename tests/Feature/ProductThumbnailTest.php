<?php

namespace Tests\Feature;

use App\Helpers\WebpImage;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductThumbnailTest extends TestCase
{
    use RefreshDatabase;

    private function storeImage(string $path, int $width, int $height): void
    {
        $image = imagecreatetruecolor($width, $height);
        ob_start();
        imagewebp($image);
        Storage::disk('public')->put($path, ob_get_clean());
    }

    public function test_cards_get_a_640px_copy_and_the_product_page_keeps_the_original(): void
    {
        Storage::fake('public');
        $this->storeImage('products/wide.webp', 1672, 941);
        $product = Product::create([
            'category_id' => Category::create(['name' => 'Uji', 'slug' => 'uji', 'is_active' => true])->id,
            'name' => 'Produk Uji',
            'slug' => 'produk-uji',
            'description' => '<p>Deskripsi.</p>',
            'image' => 'products/wide.webp',
            'is_active' => true,
        ]);

        $this->assertStringEndsWith('products/wide-640.webp', $product->thumb_url);
        $this->assertStringEndsWith('products/wide.webp', $product->image_url);
        [$width] = getimagesizefromstring(Storage::disk('public')->get('products/wide-640.webp'));
        $this->assertSame(640, $width);

        $this->storeImage('products/new.webp', 800, 450);
        $product->update(['image' => 'products/new.webp']);

        Storage::disk('public')->assertMissing('products/wide.webp');
        Storage::disk('public')->assertMissing('products/wide-640.webp');
    }

    public function test_images_already_small_enough_are_used_as_they_are(): void
    {
        Storage::fake('public');
        $this->storeImage('products/small.webp', 400, 225);

        $this->assertSame('products/small.webp', WebpImage::thumbnail('products/small.webp'));
        Storage::disk('public')->assertMissing('products/small-640.webp');
    }
}
