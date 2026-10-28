<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationalUnit extends Model
{
    protected $fillable = ['parent_id', 'code', 'label', 'slug', 'url', 'position'];

    protected $casts = [
        'parent_id' => 'integer',
        'position' => 'integer',
    ];

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position');
    }
}
