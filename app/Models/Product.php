<?php

namespace App\Models;

use App\Helpers\WebpImage;
use App\Models\BaseModel;
use App\Traits\DeletesReplacedFiles;
use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends BaseModel
{
    use Sluggable;
    use HasFactory;
    use DeletesReplacedFiles;

    protected array $storedFiles = ['image'];

    protected $fillable = [
        'uuid',
        'category_id',
        'name',
        'slug',
        'description',
        'short_description',
        'image',
        'features',
        'is_popular',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'features' => 'array',
            'is_popular' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeWithCategory($query)
    {
        return $query->with('category');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('short_description', 'like', "%{$term}%");
        });
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return str_starts_with($this->image, '/') || str_starts_with($this->image, 'http')
                ? $this->image
                : Storage::url($this->image);
        }

        return '/images/placeholder.png';
    }

    public function getThumbUrlAttribute(): string
    {
        if (!$this->image || str_starts_with($this->image, '/') || str_starts_with($this->image, 'http')) {
            return $this->image_url;
        }

        try {
            return Storage::url(WebpImage::thumbnail($this->image));
        } catch (\Throwable $e) {
            report($e);

            return $this->image_url;
        }
    }

    public function getMinPriceAttribute(): float
    {
        return $this->variants()->min('price') ?? 0;
    }

    public function getMaxPriceAttribute(): float
    {
        return $this->variants()->max('price') ?? 0;
    }

    public function getDiscountPercentageAttribute(): ?float
    {
        $variant = $this->variants()->first();

        if ($variant && $variant->original_price > $variant->price) {
            return round((($variant->original_price - $variant->price) / $variant->original_price) * 100);
        }

        return null;
    }

    public function getStockStatusAttribute(): string
    {
        $stocks = $this->variants()->where('is_active', true)->withAvailableStockCount()->get()
            ->map(fn (ProductVariant $variant) => $variant->availableStock());

        if ($stocks->containsStrict(null)) {
            return 'unlimited';
        }

        $total = $stocks->sum();

        if ($total > 10) {
            return 'in_stock';
        }

        return $total > 0 ? 'low_stock' : 'out_of_stock';
    }

    public function getSeoTitleAttribute(): string
    {
        return $this->meta_title ?: "{$this->name} - " . config('app.name');
    }

    public function getSeoDescriptionAttribute(): string
    {
        return $this->meta_description
            ?: $this->short_description
            ?: Str::limit(trim(strip_tags((string) $this->description)), 160, '');
    }
}
