<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Setting;
use App\Jobs\ProcessDigitalDelivery;

class Order extends Model
{
    public const QRISGATE = 'qrisgate';

    use HasFactory;

    protected $fillable = [
        'uuid',
        'order_number',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'payment_method_id',
        'payment_gateway',
        'voucher_code',
        'subtotal',
        'discount_amount',
        'fee_amount',
        'unique_code',
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

    protected $hidden = [
        'payment_gateway',
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

    public static function generateOrderNumber()
    {
        $prefix = Setting::get('order_number_prefix', 'DIG');
        do {
            $orderNumber = strtoupper($prefix . '-' . Str::random(7));
        } while (self::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

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

    public function getTimeRemainingAttribute()
    {
        if ($this->isExpired()) {
            return null;
        }

        return $this->expires_at->diffForHumans(null, true);
    }

    public function getFormattedSubtotalAttribute()
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    public function getFormattedFeeAmountAttribute()
    {
        return 'Rp ' . number_format($this->fee_amount, 0, ',', '.');
    }

    public function getFormattedTotalAmountAttribute()
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

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

    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    public function isPending()
    {
        return $this->payment_status === 'pending';
    }

    public function emailAmounts(): array
    {
        $discount = (int) round((float) ($this->discount_amount ?? 0));
        $uniqueCode = (int) ($this->unique_code ?? 0);

        return [
            'discount_amount' => $discount,
            'formatted_discount_amount' => 'Rp '.number_format($discount, 0, ',', '.'),
            'unique_code' => $uniqueCode,
            'formatted_unique_code' => 'Rp '.number_format($uniqueCode, 0, ',', '.'),
        ];
    }

    public function isQrisgate(): bool
    {
        return $this->payment_gateway === self::QRISGATE;
    }

    public function markAsPaidFromGateway(?\DateTimeInterface $paidAt = null, array $context = []): bool
    {
        $order = DB::transaction(function () use ($paidAt) {
            $order = static::whereKey($this->getKey())->lockForUpdate()->first();

            if (! $order || $order->status === 'paid' || $order->payment_status === 'paid') {
                return null;
            }

            $order->update([
                'status' => 'paid',
                'payment_status' => 'paid',
                'midtrans_paid_at' => $paidAt ?? now(),
            ]);

            $order->items()->where('delivery_status', 'failed')->update(['delivery_status' => 'pending']);

            return $order;
        });

        if (! $order) {
            return false;
        }

        $this->setRawAttributes($order->getAttributes(), true);

        ProcessDigitalDelivery::dispatch($order, 'auto', $context);

        return true;
    }

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

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function processedByUser()
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    public function requiresManualDelivery(): bool
    {
        return $this->orderItems()
            ->whereHas('productVariant', function ($query) {
                $query->where('delivery_type', ProductVariant::DELIVERY_MANUAL);
            })
            ->exists();
    }

    public function hasAutoDeliveryItems(): bool
    {
        return $this->orderItems()
            ->whereHas('productVariant', function ($query) {
                $query->where('delivery_type', ProductVariant::DELIVERY_AUTO);
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
