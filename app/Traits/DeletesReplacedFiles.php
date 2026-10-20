<?php

namespace App\Traits;

use App\Helpers\WebpImage;
use Illuminate\Support\Facades\Storage;

/**
 * Removes files from the public disk when the columns listed in $storedFiles are
 * replaced or their record is deleted. Public paths (/images/...) and external
 * URLs are left alone because they are not owned by the upload disk.
 */
trait DeletesReplacedFiles
{
    public static function bootDeletesReplacedFiles(): void
    {
        static::updated(function (self $model): void {
            foreach ($model->storedFiles as $column) {
                if ($model->wasChanged($column)) {
                    static::deleteStoredFile($model->getOriginal($column));
                }
            }
        });

        static::deleted(function (self $model): void {
            foreach ($model->storedFiles as $column) {
                static::deleteStoredFile($model->getAttribute($column));
            }
        });
    }

    protected static function deleteStoredFile(?string $path): void
    {
        if (blank($path) || str_starts_with($path, '/') || str_contains($path, '://')) {
            return;
        }

        Storage::disk('public')->delete([$path, WebpImage::thumbPath($path)]);
    }
}
