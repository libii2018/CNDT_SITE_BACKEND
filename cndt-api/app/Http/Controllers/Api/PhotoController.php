<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PhotoResource;
use App\Models\Album;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PhotoController extends Controller
{
    public function index(Request $request, Album $album): AnonymousResourceCollection
    {
        return PhotoResource::collection(
            $album->photos()->orderBy('position')->paginate($request->integer('per_page', 48))
        );
    }

    public function store(Request $request, Album $album): PhotoResource
    {
        $data = $request->validate([
            'path' => 'required|string|max:255',
            'caption' => 'sometimes|nullable|string|max:255',
            'position' => 'sometimes|integer',
        ]);

        return new PhotoResource($album->photos()->create($data));
    }

    public function update(Request $request, Album $album, int $photoId): PhotoResource
    {
        $photo = $album->photos()->findOrFail($photoId);
        $photo->update($request->validate([
            'path' => 'sometimes|required|string|max:255',
            'caption' => 'sometimes|nullable|string|max:255',
            'position' => 'sometimes|integer',
        ]));

        return new PhotoResource($photo);
    }

    public function destroy(Album $album, int $photoId): JsonResponse
    {
        $album->photos()->findOrFail($photoId)->delete();

        return response()->json(['message' => 'Photo supprimée.']);
    }
}
