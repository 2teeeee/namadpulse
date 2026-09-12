<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SymbolResource;
use App\Models\Symbol;
use App\Services\Screener\SymbolScreenerFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ScreenerController extends Controller
{
    private const PER_PAGE = 25;

    public function index(Request $request, SymbolScreenerFilter $screenerFilter): JsonResponse
    {
        $filters = SymbolScreenerFilter::normalizeFilters($request->all());

        $filtered = $this->runFilter($request, $screenerFilter, $filters);

        $symbols = $this->paginate($filtered, $request);

        return response()->json([
            'data' => SymbolResource::collection($symbols->items()),
            'meta' => [
                'current_page' => $symbols->currentPage(),
                'last_page' => $symbols->lastPage(),
                'total' => $symbols->total(),
            ],
        ]);
    }

    public function addToWatchlist(Request $request, SymbolScreenerFilter $screenerFilter): JsonResponse
    {
        $validated = $request->validate([
            'watchlist_id' => ['required', 'exists:watchlists,id'],
            'symbol_ids' => ['nullable', 'array'],
            'symbol_ids.*' => ['exists:symbols,id'],
        ]);

        $watchlist = $request->user()->watchlists()->findOrFail($validated['watchlist_id']);

        $symbolIds = $validated['symbol_ids'] ?? $this->runFilter(
            $request,
            $screenerFilter,
            SymbolScreenerFilter::normalizeFilters($request->all())
        )->pluck('id')->all();

        $watchlist->symbols()->syncWithoutDetaching($symbolIds);

        return response()->json([
            'message' => count($symbolIds) . " نماد به واچ‌لیست «{$watchlist->name}» اضافه شد.",
        ]);
    }

    private function runFilter(Request $request, SymbolScreenerFilter $screenerFilter, array $filters)
    {
        $allSymbols = Symbol::with(['pivotLevels', 'technicalSnapshot', 'livePrice', 'latestDailyPrice', 'group'])
            ->where('is_active', true)
            ->when($request->filled('symbol_group_id'), fn ($q) => $q->where('symbol_group_id', $request->symbol_group_id))
            ->orderBy('ticker')
            ->get();

        return $screenerFilter->apply($allSymbols, $filters);
    }

    private function paginate($items, Request $request): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $slice = $items->forPage($page, self::PER_PAGE)->values();

        return new LengthAwarePaginator(
            $slice,
            $items->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }
}
