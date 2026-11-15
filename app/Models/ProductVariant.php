<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\DigitalStock;

class ProductVariant extends Model
{
    public const DELIVERY_AUTO = 'auto';

    public const DELIVERY_MANUAL = 'manual';

    protected $fillable = [
        'product_id',
        'name',
        'description',
        'price',
        'original_price',
        'duration_days',
        'stock_quantity',
        'is_active',
        'delivery_type',
        'sort_order',
        'max_downloads',
        'download_expiry_hours',
        'delivery_template',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'duration_days' => 'integer',
            'stock_quantity' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($manual) {
                $manual->where('delivery_type', self::DELIVERY_MANUAL)
                    ->where(fn ($qty) => $qty->whereNull('stock_quantity')->orWhere('stock_quantity', '>', 0));
            })->orWhere(function ($auto) {
                $auto->where('delivery_type', self::DELIVERY_AUTO)
                    ->whereHas('digitalStocks', fn ($stock) => $stock->usable());
            });
        });
    }

    public function scopeWithAvailableStockCount($query)
    {
        return $query->withCount(['digitalStocks as available_stock_count' => fn ($stock) => $stock->usable()]);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getFormattedOriginalPriceAttribute(): string
    {
        return $this->original_price ? 'Rp ' . number_format($this->original_price, 0, ',', '.') : '';
    }

    public function getDiscountPercentageAttribute(): ?float
    {
        if ($this->original_price && $this->original_price > $this->price) {
            return round((($this->original_price - $this->price) / $this->original_price) * 100);
        }

        return null;
    }

    public function getSavingsAmountAttribute(): float
    {
        return $this->original_price ? ($this->original_price - $this->price) : 0;
    }

    public function isAutoDelivery(): bool
    {
        return $this->delivery_type === self::DELIVERY_AUTO;
    }

    public function isManualDelivery(): bool
    {
        return $this->delivery_type === self::DELIVERY_MANUAL;
    }

    public function availableStock(): ?int
    {
        if ($this->isAutoDelivery()) {
            return $this->available_stock_count;
        }

        return $this->stock_quantity === null ? null : (int) $this->stock_quantity;
    }

    public function getStockStatusAttribute(): string
    {
        $stock = $this->availableStock();

        if ($stock === null || $stock > 10) {
            return 'Tersedia';
        }

        return $stock > 0 ? "Tersisa {$stock}" : 'Habis';
    }

    public function getDurationTextAttribute(): string
    {
        if (!$this->duration_days) {
            return 'Lifetime';
        }

        if ($this->duration_days >= 365) {
            $years = floor($this->duration_days / 365);
            return $years . ' Tahun';
        } elseif ($this->duration_days >= 30) {
            $months = floor($this->duration_days / 30);
            return $months . ' Bulan';
        } else {
            return $this->duration_days . ' Hari';
        }
    }

    public function isInStock(): bool
    {
        $stock = $this->availableStock();

        return $stock === null || $stock > 0;
    }

    public function decrementStock(int $quantity = 1): bool
    {
        if ($this->isAutoDelivery() || $this->stock_quantity === null) {
            return true;
        }

        return static::whereKey($this->getKey())
            ->where('stock_quantity', '>=', $quantity)
            ->decrement('stock_quantity', $quantity) > 0;
    }

    public function digitalStocks()
    {
        return $this->hasMany(DigitalStock::class);
    }

    public function getAvailableStockCountAttribute($value): int
    {
        return $value ?? $this->digitalStocks()->usable()->count();
    }

    public function hasAvailableStock(): bool
    {
        return $this->available_stock_count > 0;
    }

    public function getNextAvailableStock(bool $lock = false): ?DigitalStock
    {
        return $this->digitalStocks()
            ->usable()
            ->orderBy('added_at', 'asc')
            ->when($lock, fn($q) => $q->lockForUpdate())
            ->first();
    }

    public function getDeliveryTemplate(): ?string
    {
        return $this->delivery_template;
    }
}
