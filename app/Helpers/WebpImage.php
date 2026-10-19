<?php

namespace App\Helpers;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Stores uploaded images as freshly encoded WebP files.
 *
 * Only decoded pixel data is written back, under a server-generated name, so the
 * client's file name, extension and any bytes outside the image data never reach
 * the public disk.
 */
class WebpImage
{
    public const QUALITY = 82;

    public const MAX_PIXELS = 25_000_000;

    private const ALLOWED_TYPES = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];

    public static function problem(string $path): ?string
    {
        $info = @getimagesize($path);

        if (!$info || !in_array($info[2], self::ALLOWED_TYPES, true)) {
            return 'File harus berupa gambar JPG, PNG, GIF, atau WebP.';
        }

        if ($info[0] * $info[1] > self::MAX_PIXELS) {
            return 'Resolusi gambar terlalu besar (maksimal 25 megapiksel).';
        }

        return null;
    }

    public static function store(UploadedFile $file, string $disk, ?string $directory, string $errorKey): string
    {
        $path = $file->getRealPath();

        if ($problem = self::problem($path)) {
            throw ValidationException::withMessages([$errorKey => $problem]);
        }

        $image = @imagecreatefromstring(file_get_contents($path));

        if (!$image) {
            throw ValidationException::withMessages([$errorKey => 'Gambar tidak dapat dibaca.']);
        }

        $image = self::applyExifOrientation($image, $path);
        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        ob_start();
        imagewebp($image, null, self::QUALITY);
        $webp = ob_get_clean();

        $target = ltrim(trim((string) $directory, '/') . '/' . Str::ulid() . '.webp', '/');
        Storage::disk($disk)->put($target, $webp, 'public');

        return $target;
    }

    /**
     * GD ignores EXIF, so photos taken in portrait would otherwise be stored sideways.
     */
    private static function applyExifOrientation(GdImage $image, string $path): GdImage
    {
        if (!function_exists('exif_read_data') || @exif_imagetype($path) !== IMAGETYPE_JPEG) {
            return $image;
        }

        $angle = match ((int) (@exif_read_data($path)['Orientation'] ?? 1)) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        return imagerotate($image, $angle, 0);
    }
}
