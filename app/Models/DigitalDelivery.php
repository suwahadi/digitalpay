<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DigitalDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'digital_stock_id',
        'download_token',
        'delivery_content',
        'delivery_status',
        'download_count',
        'max_downloads',
        'delivered_at',
        'first_download_at',
        'last_download_at',
        'expires_at',
        'email_sent',
        'email_sent_at',
        'delivery_metadata',
        'delivery_trigger',
        'manual_notes',
        'manually_triggered_at',
        'triggered_by_user_id',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'first_download_at' => 'datetime',
        'last_download_at' => 'datetime',
        'expires_at' => 'datetime',
        'email_sent_at' => 'datetime',
        'email_sent' => 'boolean',
        'delivery_metadata' => 'array',
        'manually_triggered_at' => 'datetime',
    ];

    // Konstanta untuk status
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * Relationship ke OrderItem
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Relationship ke DigitalStock
     */
    public function digitalStock(): BelongsTo
    {
        return $this->belongsTo(DigitalStock::class);
    }

    /**
     * Relationship ke Order melalui OrderItem
     */
    public function order()
    {
        return $this->hasOneThrough(
            Order::class,
            OrderItem::class,
            'id',
            'id', 
            'order_item_id',
            'order_id'
        );
    }

    /**
     * Generate unique download token saat create
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->download_token)) {
                $model->download_token = Str::random(64);
            }
        });
    }

    /**
     * Scope untuk delivery yang sudah selesai
     */
    public function scopeCompleted($query)
    {
        return $query->where('delivery_status', self::STATUS_COMPLETED);
    }

    /**
     * Scope untuk delivery yang pending
     */
    public function scopePending($query)
    {
        return $query->where('delivery_status', self::STATUS_PENDING);
    }

    /**
     * Scope untuk delivery yang expired
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Check apakah masih bisa di-download
     */
    public function canDownload(): bool
    {
        return $this->delivery_status === self::STATUS_COMPLETED
            && $this->download_count < $this->max_downloads
            && $this->expires_at->isFuture();
    }

    /**
     * Increment download count
     */
    public function incrementDownloadCount(): void
    {
        $now = now();
        
        $this->increment('download_count');
        
        if ($this->download_count === 1) {
            $this->update(['first_download_at' => $now]);
        }
        
        $this->update(['last_download_at' => $now]);
    }

    /**
     * Mark delivery sebagai completed
     */
    public function markAsCompleted(string $content): void
    {
        $this->update([
            'delivery_status' => self::STATUS_COMPLETED,
            'delivery_content' => $content,
            'delivered_at' => now()
        ]);
    }

    /**
     * Mark delivery sebagai failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'delivery_status' => self::STATUS_FAILED,
        ]);
    }

    /**
     * Generate URL untuk download
     */
    public function getDownloadUrl(): string
    {
        return route('digital.download', ['token' => $this->download_token]);
    }

    /**
     * Get remaining downloads
     */
    public function getRemainingDownloadsAttribute(): int
    {
        return max(0, $this->max_downloads - $this->download_count);
    }

    /**
     * Check if expired
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at->isPast();
    }

    public function getCanDownloadAttribute()
    {
        return $this->delivery_status === self::STATUS_COMPLETED 
        && !$this->is_expired 
        && $this->remaining_downloads > 0;
    }

    // Manual Delivery Methods
    public function triggeredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }

    public function isManualDelivery(): bool
    {
        return $this->delivery_trigger === 'manual';
    }

    public function markAsManuallyTriggered($userId = null, string $notes = null): void
    {
        $this->update([
            'delivery_trigger' => 'manual',
            'manually_triggered_at' => now(),
            'triggered_by_user_id' => $userId,
            'manual_notes' => $notes,
        ]);
    }

    // Scopes
    public function scopeManualDeliveries($query)
    {
        return $query->where('delivery_trigger', 'manual');
    }

    public function scopeAutoDeliveries($query)
    {
        return $query->where('delivery_trigger', 'auto');
    }

}
