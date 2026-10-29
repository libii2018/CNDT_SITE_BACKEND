<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    /**
     * Upload d'une image (formulaire d'édition du dashboard).
     * Le fichier est stocké dans le disque "public" de Laravel
     * (storage/app/public/uploads) et servi via /storage/…
     *
     * Sécurité : le nom est généré par hashName() et l'extension est
     * devinée à partir du contenu réel (guessExtension), jamais à
     * partir du nom fourni par l'expéditeur — un fichier "x.php" ou
     * "x.php.png" ne peut donc pas atterrir sous /storage/ avec une
     * extension exécutable.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|image|mimes:jpg,jpeg,png,webp,gif,svg|max:5120',
        ]);

        $file = $request->file('file');

        // Extension devinée du contenu réel — au revoir les .php déguisés
        $extension = strtolower($file->guessExtension() ?? '');
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true)) {
            return response()->json(['message' => 'Type de fichier non autorisé.'], 422);
        }

        // Nom aléatoire (hashName) rangé par année/mois
        $path = $file->store('uploads/'.now()->format('Y/m'), 'public');

        return response()->json([
            'path' => $path,
            'url' => $request->getSchemeAndHttpHost().'/storage/'.$path,
        ]);
    }
}
