<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'action_url',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Helper Methods
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    public function markAsUnread(): void
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    // Static Methods
    public static function send($userId, $title, $message, $type = 'info', $actionUrl = null)
    {
        return static::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'action_url' => $actionUrl,
        ]);
    }

    // Accessors
    public function getTypeBadgeAttribute(): string
    {
        $badges = [
            'info' => '<span class="badge badge-info">Info</span>',
            'success' => '<span class="badge badge-success">Success</span>',
            'warning' => '<span class="badge badge-warning">Warning</span>',
            'error' => '<span class="badge badge-danger">Error</span>',
        ];

        return $badges[$this->type] ?? '<span class="badge badge-light">Unknown</span>';
    }
}
