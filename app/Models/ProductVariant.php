<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\DigitalStock;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'description',
        'price',
        'original_price',
        'duration_days',
        'stock_quantity',
        'is_active',
        'auto_delivery',
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
            'auto_delivery' => 'boolean',
        ];
    }

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('stock_quantity')
              ->orWhere('stock_quantity', '>', 0);
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    // Accessors
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

    public function getStockStatusAttribute(): string
    {
        if ($this->stock_quantity === null) {
            return 'Tersedia';
        } elseif ($this->stock_quantity > 10) {
            return 'Tersedia';
        } elseif ($this->stock_quantity > 0) {
            return "Tersisa {$this->stock_quantity}";
        } else {
            return 'Habis';
        }
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
        return $this->stock_quantity === null || $this->stock_quantity > 0;
    }

    public function decrementStock(int $quantity = 1): bool
    {
        if ($this->stock_quantity === null) {
            return true; // Unlimited stock
        }
        
        if ($this->stock_quantity >= $quantity) {
            $this->decrement('stock_quantity', $quantity);
            return true;
        }
        
        return false;
    }

    //TAMBAHAN METHOD:

    public function digitalStocks()
    {
        return $this->hasMany(DigitalStock::class);
    }

    /**
     * Get available digital stock count
     */
    public function getAvailableStockCountAttribute(): int
    {
        return $this->digitalStocks()
                    ->where('status', DigitalStock::STATUS_AVAILABLE)
                    ->count();
    }

    /**
     * Check if has available stock
     */
    public function hasAvailableStock(): bool
    {
        return $this->available_stock_count > 0;
    }

    /**
     * Check if auto delivery enabled
     */
    public function hasAutoDelivery(): bool
    {
        return $this->auto_delivery === true;
    }

    /**
     * Get next available stock
     */
    public function getNextAvailableStock(): ?DigitalStock
    {
        return $this->digitalStocks()
            ->where('status', 'available')
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->orderBy('added_at', 'asc') //FIFO
            ->first();
    }

    /**
     * Get delivery template (from variant or product)
     */
    public function getDeliveryTemplate(): ?string
    {
        return $this->delivery_template ?? $this->product->digital_instructions;
    }

}
