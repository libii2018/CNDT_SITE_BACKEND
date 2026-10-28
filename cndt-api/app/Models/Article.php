<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'category', 'excerpt', 'content',
        'cover_image', 'author_name', 'author_role', 'author_photo', 'author_url', 'in_hero', 'hero_position',
        'is_published', 'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'in_hero' => 'boolean',
        'hero_position' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Galerie d'images de l'article */
    public function articleImages(): HasMany
    {
        return $this->hasMany(ArticleImage::class)->orderBy('position');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
