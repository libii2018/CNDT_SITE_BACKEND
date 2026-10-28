<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => trim("{$this->first_name} {$this->last_name}"),
            'slug' => $this->slug,
            'role' => $this->role,
            'unit' => $this->unit,
            'chapter' => $this->chapter,
            'email' => $this->when($request->routeIs('members.show'), $this->email),
            'phone' => $this->when($request->routeIs('members.show'), $this->phone),
            'photo' => $this->photo,
            'biography' => $this->when($request->routeIs('members.show'), $this->biography),
            'is_visible' => $this->is_visible,
            'position' => $this->position,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
