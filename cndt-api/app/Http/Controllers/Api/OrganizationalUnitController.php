<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationalUnitResource;
use App\Models\OrganizationalUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrganizationalUnitController extends Controller
{
    /**
     * Arbre complet de l'organigramme (racines = chapitres I / II / III).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $units = OrganizationalUnit::query()
            ->whereNull('parent_id')
            ->with('children.children.children')
            ->orderBy('position')
            ->get();

        return OrganizationalUnitResource::collection($units);
    }

    public function store(Request $request): OrganizationalUnitResource
    {
        $data = $request->validate([
            'parent_id' => 'sometimes|nullable|exists:organizational_units,id',
            'code' => 'required|string|max:20',
            'label' => 'required|string|max:255',
            'slug' => 'sometimes|nullable|string|max:255|unique:organizational_units,slug',
            'url' => 'sometimes|nullable|string|max:255',
            'position' => 'sometimes|integer',
        ]);

        return new OrganizationalUnitResource(OrganizationalUnit::create($data));
    }

    public function show(OrganizationalUnit $unit): OrganizationalUnitResource
    {
        return new OrganizationalUnitResource($unit->load('children'));
    }

    public function update(Request $request, OrganizationalUnit $unit): OrganizationalUnitResource
    {
        $unit->update($request->validate([
            'parent_id' => 'sometimes|nullable|exists:organizational_units,id',
            'code' => 'sometimes|required|string|max:20',
            'label' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|nullable|string|max:255|unique:organizational_units,slug',
            'url' => 'sometimes|nullable|string|max:255',
            'position' => 'sometimes|integer',
        ]));

        return new OrganizationalUnitResource($unit->load('children'));
    }

    public function destroy(OrganizationalUnit $unit): JsonResponse
    {
        $unit->delete();

        return response()->json(['message' => 'Unité supprimée.']);
    }
}
