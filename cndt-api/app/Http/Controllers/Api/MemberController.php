<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use App\Support\HtmlSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $members = Member::query()
            ->when($request->filled('unit'), fn ($q) => $q->where('unit', $request->string('unit')))
            ->when($request->filled('chapter'), fn ($q) => $q->where('chapter', $request->string('chapter')))
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($w) => $w
                ->where('first_name', 'like', '%'.$request->string('search').'%')
                ->orWhere('last_name', 'like', '%'.$request->string('search').'%')
                ->orWhere('role', 'like', '%'.$request->string('search').'%')))
            ->when($request->boolean('all'), fn ($q) => $q, fn ($q) => $q->visible())
            ->orderBy('position')
            ->paginate($request->integer('per_page', 24));

        return MemberResource::collection($members);
    }

    public function store(Request $request): MemberResource
    {
        $data = $this->validated($request);
        $data['slug'] ??= Str::slug($data['first_name'].' '.$data['last_name']).'-'.uniqid();

        return new MemberResource(Member::create($data));
    }

    public function show(Request $request, Member $member): MemberResource
    {
        if (! $member->is_visible && ! $request->user('sanctum')?->is_admin) {
            abort(404);
        }

        return new MemberResource($member);
    }

    public function update(Request $request, Member $member): MemberResource
    {
        $member->update($this->validated($request));

        return new MemberResource($member);
    }

    public function destroy(Member $member): JsonResponse
    {
        $member->delete();

        return response()->json(['message' => 'Membre supprimé.']);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'first_name' => 'sometimes|required|string|max:100',
            'last_name' => 'sometimes|required|string|max:100',
            'slug' => 'sometimes|nullable|string|max:255|unique:members,slug',
            'display_type' => 'sometimes|nullable|string|in:card,table',
            'level' => 'sometimes|nullable|string|max:255',
            'level_position' => 'sometimes|integer',
            'role' => 'sometimes|nullable|string|max:255',
            'grade' => 'sometimes|nullable|string|max:255',
            'degree' => 'sometimes|nullable|string|max:255',
            'speciality' => 'sometimes|nullable|string|max:255',
            'unit' => 'sometimes|nullable|string|max:255',
            'chapter' => 'sometimes|nullable|string|max:10',
            'email' => 'sometimes|nullable|email|max:255',
            'phone' => 'sometimes|nullable|string|max:50',
            'photo' => 'sometimes|nullable|string|max:255',
            'page_url' => 'sometimes|nullable|string|max:255',
            'biography' => 'sometimes|nullable|string',
            'is_visible' => 'sometimes|boolean',
            'position' => 'sometimes|integer',
        ]);

        // HTML riche nettoyé avant stockage (anti-XSS)
        if (array_key_exists('biography', $data)) {
            $data['biography'] = HtmlSanitizer::clean($data['biography']);
        }

        return $data;
    }
}
