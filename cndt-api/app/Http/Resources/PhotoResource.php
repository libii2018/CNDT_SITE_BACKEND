<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhotoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'album_id' => $this->album_id,
            'path' => $this->path,
            'caption' => $this->caption,
            'position' => $this->position,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
