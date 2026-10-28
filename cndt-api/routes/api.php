<?php

use App\Http\Controllers\Api\AlbumController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\OrganizationalUnitController;
use App\Http\Controllers\Api\PhotoController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\PublicationController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\InstituteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API du site vitrine CNDT
|--------------------------------------------------------------------------
| Routes publiques : lecture du contenu publié + formulaire de contact.
| Routes protégées (auth:sanctum) : administration du contenu.
| Préfixe automatique : /api/v1 (voir bootstrap/app.php).
*/

// ---------- Authentification ----------
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');

// ---------- Contenu public ----------
Route::get('/organigramme', [OrganizationalUnitController::class, 'index']);

// Carrousel des instituts (lecture publique)
Route::get('/instituts', [InstituteController::class, 'index']);
Route::get('/instituts/{institute}', [InstituteController::class, 'show']);

Route::apiResource('articles', ArticleController::class)->only(['index', 'show']);
Route::apiResource('publications', PublicationController::class)->only(['index', 'show']);
Route::apiResource('albums', AlbumController::class)->only(['index', 'show']);
Route::apiResource('members', MemberController::class)->only(['index', 'show']);
Route::apiResource('projects', ProjectController::class)->only(['index', 'show']);

// Formulaire de contact
Route::post('/contact', [MessageController::class, 'store']);

// ---------- Administration (token Sanctum + rôle admin requis) ----------
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Compteurs du tableau de bord
    Route::get('/stats', [StatsController::class, 'index']);

    // Carrousel des instituts (gestion)
    Route::apiResource('instituts', InstituteController::class)->only(['store', 'update', 'destroy'])->parameters(['instituts' => 'institute']);

    // Upload d'images (stockage Laravel public)
    Route::post('/upload', [UploadController::class, 'store']);

    Route::apiResource('articles', ArticleController::class)->only(['store', 'update', 'destroy']);

    // Galerie d'images d'un article
    Route::get('/articles/{article}/images', [ArticleController::class, 'imagesIndex']);
    Route::post('/articles/{article}/images', [ArticleController::class, 'imagesStore']);
    Route::delete('/articles/{article}/images/{image}', [ArticleController::class, 'imagesDestroy']);
    Route::apiResource('publications', PublicationController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('albums', AlbumController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('members', MemberController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('projects', ProjectController::class)->only(['store', 'update', 'destroy']);

    // Photos rattachées à un album
    Route::get('/albums/{album}/photos', [PhotoController::class, 'index']);
    Route::post('/albums/{album}/photos', [PhotoController::class, 'store']);
    Route::put('/albums/{album}/photos/{photo}', [PhotoController::class, 'update']);
    Route::delete('/albums/{album}/photos/{photo}', [PhotoController::class, 'destroy']);

    // Unités organisationnelles (organigramme)
    Route::apiResource('organizational-units', OrganizationalUnitController::class)->except(['index', 'show']);

    // Messages de contact
    Route::get('/messages', [MessageController::class, 'index']);
    Route::get('/messages/{message}', [MessageController::class, 'show']);
    Route::put('/messages/{message}', [MessageController::class, 'update']);
    Route::delete('/messages/{message}', [MessageController::class, 'destroy']);
});
