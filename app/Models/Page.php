<?php

namespace App\Models;

use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    use Sluggable;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUnpublished($query)
    {
        return $query->where('is_published', false);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getUrlAttribute()
    {
        return route('pages.show', $this->slug);
    }

}
