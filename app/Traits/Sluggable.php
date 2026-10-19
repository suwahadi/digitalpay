<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait Sluggable
{
    protected static function bootSluggable()
    {
        static::creating(function ($model) {
            if (empty($model->slug) && !empty($model->title)) {
                $model->slug = $model->generateUniqueSlug($model->title);
            } elseif (empty($model->slug) && !empty($model->name)) {
                $model->slug = $model->generateUniqueSlug($model->name);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('title') && !$model->isDirty('slug')) {
                $model->slug = $model->generateUniqueSlug($model->title);
            } elseif ($model->isDirty('name') && !$model->isDirty('slug')) {
                $model->slug = $model->generateUniqueSlug($model->name);
            }
        });
    }

    protected function generateUniqueSlug($string)
    {
        $slug = Str::slug($string);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
