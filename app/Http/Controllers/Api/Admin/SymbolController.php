<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Symbol;
use App\Models\SymbolGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SymbolController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $symbols = Symbol::with('group')
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('ticker', 'like', "%{$request->q}%")
                        ->orWhere('name', 'like', "%{$request->q}%");
                });
            })
            ->when($request->filled('symbol_group_id'), fn ($q) => $q->where('symbol_group_id', $request->symbol_group_id))
            ->orderBy('symbol_group_id')
            ->paginate(50);

        return response()->json([
            'data' => collect($symbols->items())->map($this->transform(...)),
            'meta' => [
                'current_page' => $symbols->currentPage(),
                'last_page' => $symbols->lastPage(),
                'total' => $symbols->total(),
            ],
        ]);
    }

    public function show(Symbol $symbol): JsonResponse
    {
        return response()->json([
            'data' => $this->transform($symbol),
            'symbol_groups' => SymbolGroup::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $symbol = Symbol::create($this->validated($request));

        return response()->json(['data' => $this->transform($symbol->load('group'))], 201);
    }

    public function update(Request $request, Symbol $symbol): JsonResponse
    {
        $symbol->update($this->validated($request, $symbol));

        return response()->json(['data' => $this->transform($symbol->load('group'))]);
    }

    public function destroy(Symbol $symbol): JsonResponse
    {
        $symbol->delete();

        return response()->json(['message' => 'نماد حذف شد.']);
    }

    private function validated(Request $request, ?Symbol $symbol = null): array
    {
        return $request->validate([
            'symbol_group_id' => ['required', 'exists:symbol_groups,id'],
            'ticker' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'isin_code' => ['nullable', 'string', 'max:20', 'unique:symbols,isin_code' . ($symbol ? ",{$symbol->id}" : '')],
            'tsetmc_id' => ['nullable', 'string', 'max:30', 'unique:symbols,tsetmc_id' . ($symbol ? ",{$symbol->id}" : '')],
            'board' => ['required', 'in:bourse,farabourse,other'],
            'shares_count' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function transform(Symbol $symbol): array
    {
        return [
            'id' => $symbol->id,
            'ticker' => $symbol->ticker,
            'name' => $symbol->name,
            'name_en' => $symbol->name_en,
            'isin_code' => $symbol->isin_code,
            'tsetmc_id' => $symbol->tsetmc_id,
            'board' => $symbol->board,
            'shares_count' => $symbol->shares_count,
            'is_active' => $symbol->is_active,
            'group' => $symbol->relationLoaded('group') ? [
                'id' => $symbol->group->id,
                'name' => $symbol->group->name,
            ] : null,
        ];
    }
}
