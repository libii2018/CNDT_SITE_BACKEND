<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institute extends Model
{
    protected $fillable = ['name', 'url', 'logo', 'position', 'is_visible'];

    protected $casts = [
        'position' => 'integer',
        'is_visible' => 'boolean',
    ];

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }
}
