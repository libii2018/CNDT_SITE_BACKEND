<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Album extends Model
{
    protected $fillable = ['title', 'slug', 'description', 'cover_image', 'position'];

    protected $casts = [
        'position' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class)->orderBy('position');
    }
}
