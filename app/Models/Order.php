<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Setting;
use App\Jobs\ProcessDigitalDelivery;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'order_number',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'payment_method_id',
        'voucher_code',
        'subtotal',
        'discount_amount',
        'fee_amount',
        'total_amount',
        'status',
        'payment_status',
        'notes',
        'expires_at',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'midtrans_snap_token',
        'midtrans_paid_at',
        'needs_manual_delivery',
        'delivery_method',
        'manual_delivery_queued_at',
        'manual_processed_at',
        'manual_notes',
        'processed_by_user_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'midtrans_paid_at' => 'datetime',
        'manual_delivery_queued_at' => 'datetime',
        'manual_processed_at' => 'datetime',
        'needs_manual_delivery' => 'boolean',
    ];

    /**
     * Generate a unique order number
     */
    public static function generateOrderNumber()
    {
        // Get prefix from settings
        $prefix = Setting::get('order_number_prefix', 'DIG');
        do {
            $orderNumber = strtoupper($prefix . '-' . Str::random(7));
        } while (self::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Get the user who made the order
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment method associated with the order
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the order items
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isExpired()
    {
        if (!$this->expires_at) {
            return false;
        }
        
        return $this->expires_at->isPast();
    }
    
    public function markAsExpired()
    {
        if (!in_array($this->status, ['paid', 'cancelled', 'expired'])) {
            $this->update([
                'status' => 'expired',
                'payment_status' => 'expired'
            ]);
        }
    }

    /**
     * Get time remaining until expiration
     */
    public function getTimeRemainingAttribute()
    {
        if ($this->isExpired()) {
            return null;
        }
        
        return $this->expires_at->diffForHumans(null, true);
    }

    /**
     * Get formatted subtotal
     */
    public function getFormattedSubtotalAttribute()
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    /**
     * Get formatted fee amount
     */
    public function getFormattedFeeAmountAttribute()
    {
        return 'Rp ' . number_format($this->fee_amount, 0, ',', '.');
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAmountAttribute()
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'yellow',
            'paid' => 'green',
            'cancelled' => 'red',
            'expired' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Menunggu',
            'paid' => 'Lunas',
            'cancelled' => 'Dibatalkan',
            'expired' => 'Kedaluwarsa',
            default => 'Unknown'
        };
    }

    /**
     * Get payment status label
     */
    public function getPaymentStatusLabelAttribute()
    {
        return match($this->payment_status) {
            'unpaid' => 'Belum Dibayar',
            'pending' => 'Menunggu Konfirmasi',
            'paid' => 'Lunas',
            'cancelled' => 'Dibatalkan',
            'expired' => 'Kedaluwarsa',
            'failed' => 'Gagal',
            default => 'Unknown'
        };
    }

    /**
     * Check if payment is paid
     */
    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if payment is pending
     */
    public function isPending()
    {
        return $this->payment_status === 'pending';
    }

    /**
     * Update payment status from Midtrans
     */
    public function updatePaymentStatus($transactionStatus, $fraudStatus = null, $response = null)
    {
        $oldPaymentStatus = $this->payment_status;
        $paymentStatus = $this->mapMidtransStatus($transactionStatus, $fraudStatus);
        
        $updateData = [
            'payment_status' => $paymentStatus,
        ];

        if ($paymentStatus === 'paid') {
            $updateData['midtrans_paid_at'] = now();
            $updateData['status'] = 'paid';
        } elseif (in_array($paymentStatus, ['cancelled', 'expired', 'failed'])) {
            $updateData['status'] = $paymentStatus;
        }

        $this->update($updateData);
        
        if ($oldPaymentStatus !== 'paid' && $paymentStatus === 'paid') {
            ProcessDigitalDelivery::dispatch($this, 'auto', [
                'triggered_by' => 'payment_callback',
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus
            ]);
        }
    }

    /**
     * Map Midtrans status to our payment status
     */
    private function mapMidtransStatus($transactionStatus, $fraudStatus = null)
    {
        return match($transactionStatus) {
            'capture' => $fraudStatus === 'accept' ? 'paid' : 'pending',
            'settlement' => 'paid',
            'pending' => 'pending',
            'deny', 'cancel' => 'cancelled',
            'expire' => 'expired',
            'failure' => 'failed',
            default => 'pending'
        };
    }

    /**
     * Get the payment record associated with this order
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Get all payments associated with this order
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Manual Delivery Methods
    public function processedByUser()
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    public function requiresManualDelivery(): bool
    {
        return $this->orderItems()
            ->whereHas('productVariant', function ($query) {
                $query->where('auto_delivery', false);
            })
            ->exists();
    }

    public function hasAutoDeliveryItems(): bool
    {
        return $this->orderItems()
            ->whereHas('productVariant', function ($query) {
                $query->where('auto_delivery', true);
            })
            ->exists();
    }

    public function getDeliveryMethod(): string
    {
        $hasAuto = $this->hasAutoDeliveryItems();
        $hasManual = $this->requiresManualDelivery();

        if ($hasAuto && $hasManual) {
            return 'mixed';
        } elseif ($hasManual) {
            return 'manual';
        } else {
            return 'auto';
        }
    }

    public function markAsManualDeliveryQueued(): void
    {
        $this->update([
            'needs_manual_delivery' => true,
            'delivery_method' => $this->getDeliveryMethod(),
            'manual_delivery_queued_at' => now(),
        ]);
    }

    public function markAsManuallyProcessed($userId = null, string $notes = null): void
    {
        $this->update([
            'needs_manual_delivery' => false,
            'manual_processed_at' => now(),
            'manual_notes' => $notes,
            'processed_by_user_id' => $userId,
        ]);
    }

    // Scopes untuk manual delivery
    public function scopePendingManualDelivery($query)
    {
        return $query->where('needs_manual_delivery', true)
                    ->where('status', 'paid');
    }

    public function scopeManuallyProcessed($query)
    {
        return $query->whereNotNull('manual_processed_at');
    }

}
