<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key_name',
        'value',
        'type',
        'group_name',
        'description',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    // Scopes
    public function scopeGroup($query, $group)
    {
        return $query->where('group_name', $group);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    // Static Methods
    public static function get($key, $default = null)
    {
        $setting = static::where('key_name', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    public static function set($key, $value, $type = 'string', $group = 'general')
    {
        return static::updateOrCreate(
            ['key_name' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group_name' => $group,
            ]
        );
    }

    protected static function castValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'number':
                return is_numeric($value) ? (float) $value : 0;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    // Helper Methods
    public function getCastedValueAttribute()
    {
        return static::castValue($this->value, $this->type);
    }
}
