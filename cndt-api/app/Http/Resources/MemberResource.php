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
            'display_type' => $this->display_type ?? 'card',
            'level' => $this->level,
            'level_position' => $this->level_position,
            'role' => $this->role,
            'grade' => $this->grade,
            'degree' => $this->degree,
            'speciality' => $this->speciality,
            'since' => $this->since,
            'unit' => $this->unit,
            'chapter' => $this->chapter,
            'email' => $this->when($request->routeIs('members.show'), $this->email),
            'phone' => $this->when($request->routeIs('members.show'), $this->phone),
            'photo' => $this->photo,
            'page_url' => $this->page_url,
            'biography' => $this->when($request->routeIs('members.show'), $this->biography),
            'interests' => $this->when($request->routeIs('members.show'), $this->interests),
            'is_visible' => $this->is_visible,
            'position' => $this->position,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
