<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InstituteResource;
use App\Models\Institute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InstituteController extends Controller
{
    /**
     * Instituts du carrousel (triés par position). Le paramètre ?all=true
     * (réservé aux admins) inclut les éléments masqués.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $institutes = Institute::query()
            ->when($request->boolean('all') && $request->user('sanctum')?->is_admin,
                fn ($q) => $q, fn ($q) => $q->visible())
            ->orderBy('position')
            ->paginate($request->integer('per_page', 100));

        return InstituteResource::collection($institutes);
    }

    public function store(Request $request): InstituteResource
    {
        $data = $this->validated($request);
        $data['position'] ??= Institute::max('position') + 1;

        return new InstituteResource(Institute::create($data));
    }

    public function show(Institute $institute): InstituteResource
    {
        return new InstituteResource($institute);
    }

    public function update(Request $request, Institute $institute): InstituteResource
    {
        $institute->update($this->validated($request));

        return new InstituteResource($institute);
    }

    public function destroy(Institute $institute): JsonResponse
    {
        $institute->delete();

        return response()->json(['message' => 'Institut supprimé du carrousel.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'url' => 'sometimes|nullable|string|max:2048',
            'logo' => 'sometimes|required|string|max:2048',
            'position' => 'sometimes|integer',
            'is_visible' => 'sometimes|boolean',
        ]);
    }
}
