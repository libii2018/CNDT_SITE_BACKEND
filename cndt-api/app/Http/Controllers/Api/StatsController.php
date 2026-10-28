<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Article;
use App\Models\Member;
use App\Models\Message;
use App\Models\Photo;
use App\Models\Project;
use App\Models\Publication;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    /**
     * Compteurs pour le tableau de bord d'administration.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'articles' => Article::count(),
            'published_articles' => Article::published()->count(),
            'publications' => Publication::count(),
            'albums' => Album::count(),
            'photos' => Photo::count(),
            'members' => Member::count(),
            'projects' => Project::count(),
            'messages' => Message::count(),
            'unread_messages' => Message::where('is_read', false)->count(),
        ]);
    }
}
