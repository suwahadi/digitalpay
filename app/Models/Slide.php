<?php

namespace App\Models;

use App\Traits\DeletesReplacedFiles;
use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    use DeletesReplacedFiles;

    protected array $storedFiles = ['image'];

    protected $fillable = [
        'image',
        'title',
        'link',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getImageUrlAttribute(): string
    {
        if (!$this->image) {
            return '/images/placeholder.png';
        }

        if (str_starts_with($this->image, '/') || filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        return asset('storage/' . $this->image);
    }
}
