<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

abstract class BaseModel extends Model
{
    /**
     * Generate UUID for model
     */
    protected static function bootBaseModel()
    {
        static::creating(function ($model) {
            if (empty($model->uuid) && in_array('uuid', $model->getFillable())) {
                $model->uuid = Str::uuid();
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
