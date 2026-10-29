<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'first_name', 'last_name', 'slug', 'display_type', 'level', 'level_position',
        'role', 'grade', 'degree', 'speciality', 'unit', 'chapter',
        'email', 'phone', 'photo', 'biography', 'is_visible', 'position',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'position' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }
}
