<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'max_discount',
        'min_total',
        'starts_at',
        'expires_at',
        'is_active',
        'usage_limit',
        'used_count',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'min_total' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValidDate($query)
    {
        return $query
            ->where(function ($q) { $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()); })
            ->where(function ($q) { $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()); });
    }

    public function isApplicable(float $subtotal): bool
    {
        if (!$this->is_active) return false;
        if ($this->starts_at && $this->starts_at->isFuture()) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->min_total && $subtotal < (float) $this->min_total) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if (!$this->isApplicable($subtotal)) return 0.0;

        if ($this->discount_type === 'fixed') {
            return min((float) $this->discount_value, $subtotal);
        }

        $discount = ($subtotal * (float) $this->discount_value) / 100.0;
        if ($this->max_discount) {
            $discount = min($discount, (float) $this->max_discount);
        }
        return $discount;
    }
}
