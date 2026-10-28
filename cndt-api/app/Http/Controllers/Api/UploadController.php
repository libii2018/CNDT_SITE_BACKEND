<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * Upload d'une image (formulaire d'édition du dashboard).
     * Le fichier est stocké dans le disque "public" de Laravel
     * (storage/app/public/uploads) et servi via /storage/…
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|image|mimes:jpg,jpeg,png,webp,gif,svg|max:5120',
        ]);

        $file = $request->file('file');

        // Nom lisible + identifiant court, rangé par année/mois
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            . '-' . Str::random(6) . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs('uploads/'.now()->format('Y/m'), $name, 'public');

        return response()->json([
            'path' => $path,
            'url' => $request->getSchemeAndHttpHost().'/storage/'.$path,
        ]);
    }
}
