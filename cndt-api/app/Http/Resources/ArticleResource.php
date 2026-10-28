<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'category' => $this->category,
            'excerpt' => $this->excerpt,
            'content' => $this->when($request->routeIs('articles.show') || $request->user('sanctum')?->is_admin, $this->content),
            'cover_image' => $this->cover_image,
            'author_name' => $this->author_name,
            'author_role' => $this->author_role,
            'author_photo' => $this->author_photo,
            'author_url' => $this->author_url,
            'is_published' => $this->is_published,
            'images' => ArticleImageResource::collection($this->whenLoaded('articleImages')),
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
