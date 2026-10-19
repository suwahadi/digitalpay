<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends BaseModel
{
    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'uuid',
        'order_id',
        'user_id',
        'payment_method_id',
        'amount',
        'fee_amount',
        'total_amount',
        'currency',
        'customer_type',
        'customer_email',
        'customer_phone',
        'gateway_provider',
        'gateway_transaction_id',
        'gateway_reference',
        'gateway_response',
        'status',
        'payment_url',
        'callback_url',
        'return_url',
        'paid_at',
        'expired_at',
        // Midtrans fields
        'midtrans_snap_token',
        'midtrans_transaction_status',
        'midtrans_fraud_status',
        'midtrans_payment_type',
        'midtrans_gross_amount',
        'midtrans_signature_key',
        'midtrans_raw_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'midtrans_gross_amount' => 'decimal:2',
            'gateway_response' => 'array',
            'midtrans_raw_response' => 'array',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PaymentLog::class);
    }

    // Scopes
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeFailed($query)
    {
        return $query->whereIn('status', [self::STATUS_FAILED, self::STATUS_CANCELLED, self::STATUS_EXPIRED]);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Helper Methods
    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isFailed(): bool
    {
        return in_array($this->status, [self::STATUS_FAILED, self::STATUS_CANCELLED, self::STATUS_EXPIRED]);
    }

    public function isExpired(): bool
    {
        if (!$this->expired_at) {
            return false;
        }
        
        return $this->expired_at->isPast();
    }
    
    public function markAsExpired(): void
    {
        if (!in_array($this->status, [self::STATUS_SUCCESS, self::STATUS_CANCELLED, self::STATUS_EXPIRED])) {
            $this->update([
                'status' => self::STATUS_EXPIRED,
            ]);
            
            if ($this->order && !in_array($this->order->status, ['paid', 'cancelled', 'expired'])) {
                $this->order->update([
                    'status' => 'expired',
                    'payment_status' => 'expired'
                ]);
            }
        }
    }

    public function markAsPaid($gatewayResponse = null): void
    {
        $this->update([
            'status' => self::STATUS_SUCCESS,
            'paid_at' => now(),
            'gateway_response' => $gatewayResponse,
        ]);

        // Update order payment status
        $this->order->update(['payment_status' => 'paid']);
    }

    public function markAsFailed($gatewayResponse = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'gateway_response' => $gatewayResponse,
        ]);
    }

    /**
     * Update from Midtrans notification
     */
    public function updateFromMidtransNotification($notificationData)
    {
        $this->update([
            'midtrans_transaction_status' => $notificationData['transaction_status'] ?? null,
            'midtrans_fraud_status' => $notificationData['fraud_status'] ?? null,
            'midtrans_payment_type' => $notificationData['payment_type'] ?? null,
            'midtrans_gross_amount' => $notificationData['gross_amount'] ?? null,
            'midtrans_signature_key' => $notificationData['signature_key'] ?? null,
            'midtrans_raw_response' => $notificationData,
            'gateway_response' => $notificationData,
        ]);

        // Update status based on transaction status
        $this->updateStatusFromMidtrans($notificationData['transaction_status'], $notificationData['fraud_status'] ?? null);
    }

    /**
     * Update payment status based on Midtrans status
     */
    public function updateStatusFromMidtrans($transactionStatus, $fraudStatus = null)
    {
        $newStatus = $this->mapMidtransStatusToPaymentStatus($transactionStatus, $fraudStatus);
        
        $updateData = ['status' => $newStatus];
        
        if ($newStatus === self::STATUS_SUCCESS) {
            $updateData['paid_at'] = now();
        }
        
        $this->update($updateData);
        
        // Also update the order
        $this->order->updatePaymentStatus($transactionStatus, $fraudStatus, $this->midtrans_raw_response);
    }

    /**
     * Map Midtrans status to payment status
     */
    private function mapMidtransStatusToPaymentStatus($transactionStatus, $fraudStatus = null)
    {
        return match($transactionStatus) {
            'capture' => $fraudStatus === 'accept' ? self::STATUS_SUCCESS : self::STATUS_PROCESSING,
            'settlement' => self::STATUS_SUCCESS,
            'pending' => self::STATUS_PENDING,
            'deny', 'cancel' => self::STATUS_CANCELLED,
            'expire' => self::STATUS_EXPIRED,
            'failure' => self::STATUS_FAILED,
            default => self::STATUS_PENDING
        };
    }

    /**
     * Check if this payment uses Midtrans
     */
    public function isMidtransPayment()
    {
        return $this->gateway_provider === 'midtrans';
    }

    /**
     * Get Midtrans payment URL
     */
    public function getMidtransPaymentUrl()
    {
        if (!$this->midtrans_snap_token) {
            return null;
        }

        $baseUrl = config('midtrans.is_production') 
            ? config('midtrans.snap_url.production')
            : config('midtrans.snap_url.sandbox');
            
        return $baseUrl . '/' . $this->midtrans_snap_token;
    }

    // Accessors
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'pending' => '<span class="badge badge-warning">Pending</span>',
            'processing' => '<span class="badge badge-info">Processing</span>',
            'success' => '<span class="badge badge-success">Success</span>',
            'failed' => '<span class="badge badge-danger">Failed</span>',
            'cancelled' => '<span class="badge badge-secondary">Cancelled</span>',
            'expired' => '<span class="badge badge-dark">Expired</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge badge-light">Unknown</span>';
    }
}
