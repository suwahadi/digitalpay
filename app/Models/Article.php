<?php

namespace App\Models;

use App\Traits\DeletesReplacedFiles;
use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use Sluggable;
    use DeletesReplacedFiles;

    protected array $storedFiles = ['featured_image'];

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'author_id',
        'category',
        'tags',
        'is_published',
        'is_featured',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                     ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('content', 'like', "%{$term}%")
              ->orWhere('excerpt', 'like', "%{$term}%");
        });
    }

    public function getFeaturedImageUrlAttribute(): string
    {
        if ($this->featured_image) {
            return $this->featured_image;
        }
        
        return asset('images/placeholder.png');
    }

    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return ceil($wordCount / 200); // Assuming 200 words per minute
    }

    public function getSeoTitleAttribute(): string
    {
        return $this->title . ' - ' . config('app.name');
    }

    public function getFormattedPublishedDateAttribute(): string
    {
        return $this->published_at ? $this->published_at->format('d M Y') : '';
    }
}
