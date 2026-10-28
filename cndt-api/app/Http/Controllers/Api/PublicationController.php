<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicationResource;
use App\Models\Publication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class PublicationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $publications = Publication::query()
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->string('search').'%'))
            ->when($request->boolean('all'), fn ($q) => $q, fn ($q) => $q->published())
            ->latest('published_at')
            ->paginate($request->integer('per_page', 12));

        return PublicationResource::collection($publications);
    }

    public function store(Request $request): PublicationResource
    {
        $data = $this->validated($request);
        $data['slug'] ??= Str::slug($data['title']).'-'.uniqid();
        $data['published_at'] ??= now();

        return new PublicationResource(Publication::create($data));
    }

    public function show(Request $request, Publication $publication): PublicationResource
    {
        if (! $publication->is_published && ! $request->user('sanctum')?->is_admin) {
            abort(404);
        }

        return new PublicationResource($publication);
    }

    public function update(Request $request, Publication $publication): PublicationResource
    {
        $publication->update($this->validated($request));

        return new PublicationResource($publication);
    }

    public function destroy(Publication $publication): JsonResponse
    {
        $publication->delete();

        return response()->json(['message' => 'Publication supprimée.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|nullable|string|max:255|unique:publications,slug',
            'type' => 'sometimes|nullable|string|max:100',
            'description' => 'sometimes|nullable|string',
            'file_path' => 'sometimes|nullable|string|max:255',
            'cover_image' => 'sometimes|nullable|string|max:255',
            'is_published' => 'sometimes|boolean',
            'published_at' => 'sometimes|nullable|date',
        ]);
    }
}
