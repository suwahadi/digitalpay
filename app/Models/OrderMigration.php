<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderMigration extends Model
{
    protected $fillable = [
        'guest_session_id',
        'user_id',
        'orders_migrated_count',
        'migration_method',
        'migrated_by_admin_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'orders_migrated_count' => 'integer',
        ];
    }

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($migration) {
            $migration->created_at = now();
        });
    }

    // Relationships
    public function guestSession(): BelongsTo
    {
        return $this->belongsTo(GuestSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function migratedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'migrated_by_admin_id');
    }

    // Scopes
    public function scopeMethod($query, $method)
    {
        return $query->where('migration_method', $method);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
