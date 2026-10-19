<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'subject_id' => 'integer',
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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeSubjectType($query, $type)
    {
        return $query->where('subject_type', $type);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Static Methods
    public static function log($action, $description = null, $subject = null, $properties = [])
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
