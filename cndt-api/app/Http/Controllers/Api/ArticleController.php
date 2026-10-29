<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\ArticleImageResource;
use App\Models\Article;
use App\Support\HtmlSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $articles = Article::query()
            ->when($request->boolean('hero'), fn ($q) => $q->where('in_hero', true)->orderBy('hero_position'))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($w) => $w
                ->where('title', 'like', '%'.$request->string('search').'%')
                ->orWhere('excerpt', 'like', '%'.$request->string('search').'%')))
            ->when($request->boolean('all'), fn ($q) => $q, fn ($q) => $q->published())
            ->when($request->user('sanctum')?->is_admin, fn ($q) => $q->with('articleImages'))
            ->latest('published_at')
            ->paginate($request->integer('per_page', 12));

        return ArticleResource::collection($articles);
    }

    public function store(Request $request): ArticleResource
    {
        $data = $this->validated($request);
        $data['slug'] ??= $this->uniqueSlug($data['title']);
        $data['published_at'] ??= now();
        if (! empty($data['in_hero'])) {
            $data['hero_position'] ??= Article::where('in_hero', true)->max('hero_position') + 1;
        }

        return new ArticleResource(Article::create($data));
    }

    public function show(Request $request, Article $article): ArticleResource
    {
        if (! $article->is_published && ! $request->user('sanctum')?->is_admin) {
            abort(404);
        }

        return new ArticleResource($article->load('articleImages'));
    }

    /* ====== Galerie d'images de l'article ====== */

    public function imagesIndex(Request $request, Article $article): AnonymousResourceCollection
    {
        return ArticleImageResource::collection($article->articleImages()->get());
    }

    public function imagesStore(Request $request, Article $article): ArticleImageResource
    {
        $data = $request->validate([
            'path' => 'required|string|max:2048',
            'caption' => 'sometimes|nullable|string|max:255',
            'position' => 'sometimes|integer',
        ]);
        $data['position'] ??= $article->articleImages()->count() + 1;

        return new ArticleImageResource($article->articleImages()->create($data));
    }

    public function imagesDestroy(Article $article, int $image): JsonResponse
    {
        $article->articleImages()->findOrFail($image)->delete();

        return response()->json(['message' => 'Image retirée de la galerie.']);
    }

    public function update(Request $request, Article $article): ArticleResource
    {
        $data = $this->validated($request);
        if (isset($data['title']) && empty($data['slug'])) {
            $data['slug'] = $this->uniqueSlug($data['title'], $article->id);
        }

        if (! empty($data['in_hero']) && ($data['hero_position'] ?? null) === null) {
            $data['hero_position'] = Article::where('in_hero', true)->where('id', '!=', $article->id)->max('hero_position') + 1;
        }
        $article->update($data);

        return new ArticleResource($article);
    }

    public function destroy(Article $article): JsonResponse
    {
        $article->delete();

        return response()->json(['message' => 'Article supprimé.']);
    }

    private function validated(Request $request): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'slug' => 'sometimes|nullable|string|max:255|unique:articles,slug',
            'category' => 'sometimes|nullable|string|max:100',
            'excerpt' => 'sometimes|nullable|string',
            'content' => 'sometimes|nullable|string',
            'cover_image' => 'sometimes|nullable|string|max:255',
            'author_name' => 'sometimes|nullable|string|max:255',
            'author_role' => 'sometimes|nullable|string|max:255',
            'author_photo' => 'sometimes|nullable|string|max:2048',
            'author_url' => 'sometimes|nullable|string|max:2048',
            'in_hero' => 'sometimes|boolean',
            'hero_position' => 'sometimes|nullable|integer',
            'is_published' => 'sometimes|boolean',
            'published_at' => 'sometimes|nullable|date',
        ];

        if ($request->isMethod('PATCH') || $request->isMethod('PUT')) {
            $rules['title'] = 'sometimes|required|string|max:255';
        }

        $data = $request->validate($rules);

        // Le HTML riche est nettoyé avant stockage (anti-XSS) : voir
        // App\Support\HtmlSanitizer — le contenu est réaffiché dans des
        // pages du même domaine que le dashboard.
        if (array_key_exists('content', $data)) {
            $data['content'] = HtmlSanitizer::clean($data['content']);
        }

        return $data;
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;

        $query = Article::where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }
        while ($query->exists()) {
            $slug = $base.'-'.++$i;
            $query = Article::where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
        }

        return $slug;
    }
}
