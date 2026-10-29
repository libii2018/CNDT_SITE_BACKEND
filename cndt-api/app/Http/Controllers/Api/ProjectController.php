<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Support\HtmlSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $projects = Project::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->string('search').'%'))
            ->when($request->boolean('all'), fn ($q) => $q, fn ($q) => $q->published())
            ->latest()
            ->paginate($request->integer('per_page', 12));

        return ProjectResource::collection($projects);
    }

    public function store(Request $request): ProjectResource
    {
        $data = $this->validated($request);
        $data['slug'] ??= Str::slug($data['title']).'-'.uniqid();

        return new ProjectResource(Project::create($data));
    }

    public function show(Request $request, Project $project): ProjectResource
    {
        if (! $project->is_published && ! $request->user('sanctum')?->is_admin) {
            abort(404);
        }

        return new ProjectResource($project);
    }

    public function update(Request $request, Project $project): ProjectResource
    {
        $project->update($this->validated($request));

        return new ProjectResource($project);
    }

    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return response()->json(['message' => 'Projet supprimé.']);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|nullable|string|max:255|unique:projects,slug',
            'status' => 'sometimes|nullable|string|in:a_venir,en_cours,termine',
            'summary' => 'sometimes|nullable|string',
            'description' => 'sometimes|nullable|string',
            'partner' => 'sometimes|nullable|string|max:255',
            'cover_image' => 'sometimes|nullable|string|max:255',
            'link' => 'sometimes|nullable|url|max:255',
            'start_date' => 'sometimes|nullable|date',
            'end_date' => 'sometimes|nullable|date|after_or_equal:start_date',
            'is_published' => 'sometimes|boolean',
        ]);

        // HTML riche nettoyé avant stockage (anti-XSS)
        if (array_key_exists('description', $data)) {
            $data['description'] = HtmlSanitizer::clean($data['description']);
        }

        return $data;
    }
}
