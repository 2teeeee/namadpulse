<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SymbolGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SymbolGroupController extends Controller
{
    public function index(): JsonResponse
    {
        $symbolGroups = SymbolGroup::withCount('symbols')->with('parent')->orderBy('name')->paginate(50);

        return response()->json([
            'data' => collect($symbolGroups->items())->map(fn (SymbolGroup $g) => [
                'id' => $g->id,
                'name' => $g->name,
                'code' => $g->code,
                'parent' => $g->parent?->name,
                'symbols_count' => $g->symbols_count,
                'is_active' => $g->is_active,
            ]),
            'meta' => [
                'current_page' => $symbolGroups->currentPage(),
                'last_page' => $symbolGroups->lastPage(),
                'total' => $symbolGroups->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:symbol_groups,code'],
            'parent_id' => ['nullable', 'exists:symbol_groups,id'],
        ]);

        $group = SymbolGroup::create($validated);

        return response()->json(['data' => $group], 201);
    }

    public function update(Request $request, SymbolGroup $symbolGroup): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        $symbolGroup->update($validated);

        return response()->json(['data' => $symbolGroup]);
    }

    public function destroy(SymbolGroup $symbolGroup): JsonResponse
    {
        $symbolGroup->delete();

        return response()->json(['message' => 'گروه نماد حذف شد.']);
    }
}
