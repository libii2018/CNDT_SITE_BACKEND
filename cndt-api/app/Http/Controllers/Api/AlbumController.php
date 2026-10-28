<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlbumResource;
use App\Models\Album;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class AlbumController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $albums = Album::query()
            ->with('photos')
            ->orderBy('position')
            ->paginate($request->integer('per_page', 24));

        return AlbumResource::collection($albums);
    }

    public function store(Request $request): AlbumResource
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'sometimes|nullable|string|max:255|unique:albums,slug',
            'description' => 'sometimes|nullable|string',
            'cover_image' => 'sometimes|nullable|string|max:255',
            'position' => 'sometimes|integer',
        ]);
        $data['slug'] ??= Str::slug($data['title']).'-'.uniqid();

        return new AlbumResource(Album::create($data)->load('photos'));
    }

    public function show(Request $request, Album $album): AlbumResource
    {
        return new AlbumResource($album->load('photos'));
    }

    public function update(Request $request, Album $album): AlbumResource
    {
        $album->update($request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|nullable|string|max:255|unique:albums,slug',
            'description' => 'sometimes|nullable|string',
            'cover_image' => 'sometimes|nullable|string|max:255',
            'position' => 'sometimes|integer',
        ]));

        return new AlbumResource($album->load('photos'));
    }

    public function destroy(Album $album): JsonResponse
    {
        $album->delete();

        return response()->json(['message' => 'Album supprimé.']);
    }
}
