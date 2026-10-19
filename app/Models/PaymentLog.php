<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentLog extends Model
{
    protected $fillable = [
        'payment_id',
        'event_type',
        'request_data',
        'response_data',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'request_data' => 'array',
            'response_data' => 'array',
        ];
    }

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($log) {
            $log->created_at = now();
        });
    }

    // Relationships
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    // Scopes
    public function scopeEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
