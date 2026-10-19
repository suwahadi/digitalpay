<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DigitalStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_variant_id',
        'stock_name',
        'stock_data',
        'status',
        'added_at',
        'added_by_user_id',
        'assigned_at',
        'assigned_to_order_item_id',
        'delivered_at',
    ];

    protected $casts = [
        'stock_data' => 'array',
        'added_at' => 'datetime',
        'assigned_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // Konstanta untuk status
    const STATUS_AVAILABLE = 'available';
    const STATUS_RESERVED = 'reserved';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_EXPIRED = 'expired';

    /**
     * Relationship ke ProductVariant
     * Satu stock belong to satu product variant
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Relationship ke User (admin yang menambahkan stock)
     */
    public function addedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }

    /**
     * Relationship ke OrderItem (order yang menggunakan stock ini)
     */
    public function assignedToOrderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'assigned_to_order_item_id');
    }

    /**
     * Relationship ke DigitalDelivery
     * Satu stock bisa punya banyak delivery attempts (jika gagal)
     */
    public function digitalDeliveries(): HasMany
    {
        return $this->hasMany(DigitalDelivery::class);
    }

    /**
     * Scope untuk mengambil stock yang available
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    /**
     * Scope untuk mengambil stock dari variant tertentu
     */
    public function scopeForVariant($query, $variantId)
    {
        return $query->where('product_variant_id', $variantId);
    }

    /**
     * Method untuk assign stock ke order item
     */
    public function assignToOrderItem(OrderItem $orderItem): bool
    {
        if ($this->status !== self::STATUS_AVAILABLE) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_RESERVED,
            'assigned_to_order_item_id' => $orderItem->id,
            'assigned_at' => now()
        ]);

        return true;
    }

    /**
     * Method untuk mark stock sebagai delivered
     */
    public function markAsDelivered(): void
    {
        $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivered_at' => now()
        ]);
    }

    /**
     * Check apakah stock masih valid (belum expired)
     */
    public function isValid()
    {
        return $this->status === 'available';
    }

    /**
     * Get formatted stock data untuk display
     */
    public function getFormattedStockDataAttribute(): string
    {
        $data = $this->stock_data;
        $formatted = [];
        
        foreach ($data as $key => $value) {
            $formatted[] = ucfirst($key) . ': ' . $value;
        }
        
        return implode("\n", $formatted);
    }
}
