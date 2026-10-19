<?php

namespace App\Models;

use App\Models\BaseModel;
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
        'is_digital',
        'digital_instructions',
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
            'is_digital' => 'boolean',
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
            return Storage::url($this->image);
        }
        
        return Storage::url('images/default-product.png');
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
        $variants = $this->variants()->where('is_active', true);

        if ((clone $variants)->whereNull('stock_quantity')->exists()) {
            return 'unlimited';
        }

        $totalStock = $variants->sum('stock_quantity');

        if ($totalStock > 10) {
            return 'in_stock';
        } elseif ($totalStock > 0) {
            return 'low_stock';
        } else {
            return 'out_of_stock';
        }
    }

    public function getSeoTitleAttribute(): string
    {
        return $this->meta_title ?: "{$this->name} - " . config('app.name');
    }

    public function getSeoDescriptionAttribute(): string
    {
        return $this->meta_description ?: $this->short_description;
    }

    public function isDigital(): bool
    {
        return $this->is_digital === true;
    }

    /**
     * Get digital template for this product
     */
    public function getDigitalTemplate(): ?string
    {
        if (!$this->isDigital()) {
            return null;
        }
        
        return $this->digital_instructions;
    }

    /**
     * Get variants that have digital stock
     */
    public function digitalVariants()
    {
        return $this->variants()
            ->where('auto_delivery', true);
    }

    /**
     * Scope untuk produk digital
     */
    public function scopeDigital($query)
    {
        return $query->where('is_digital', true);
    }

    /**
     * Scope untuk produk fisik
     */
    public function scopePhysical($query)
    {
        return $query->where('is_digital', false);
    }

}
