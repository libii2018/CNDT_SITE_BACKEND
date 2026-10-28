<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationalUnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'code' => $this->code,
            'label' => $this->label,
            'slug' => $this->slug,
            'url' => $this->url,
            'position' => $this->position,
            'children' => self::collection($this->whenLoaded('children')),
        ];
    }
}
