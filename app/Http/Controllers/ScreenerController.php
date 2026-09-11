<?php

namespace App\Http\Controllers;

use App\Models\Symbol;
use App\Models\SymbolGroup;
use App\Services\Screener\SymbolScreenerFilter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class ScreenerController extends Controller
{
    private const PER_PAGE = 25;

    public function index(Request $request, SymbolScreenerFilter $screenerFilter): View
    {
        $filters = SymbolScreenerFilter::normalizeFilters($request->all());

        $allSymbols = Symbol::with([
                'pivotLevels',
                'technicalSnapshot',
                'livePrice',
                'latestDailyPrice',
                'group',
            ])
            ->where('is_active', true)
            ->when($request->filled('symbol_group_id'), function ($query) use ($request) {
                $query->where('symbol_group_id', $request->symbol_group_id);
            })
            ->orderBy('ticker')
            ->get();

        $filtered = $screenerFilter->apply($allSymbols, $filters);

        $symbols = $this->paginate($filtered, $request);

        $symbolGroups = SymbolGroup::orderBy('name')->get();

        return view('symbols.screener', compact('symbols', 'filters', 'symbolGroups'));
    }

    public function addToWatchlist(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'watchlist_id' => ['required', 'exists:watchlists,id'],
            'symbol_ids' => ['nullable', 'array'],
            'symbol_ids.*' => ['exists:symbols,id'],
        ]);

        $watchlist = $request->user()->watchlists()->findOrFail($validated['watchlist_id']);

        // اگر کاربر نمادی رو تیک نزده بود، یعنی تمام نتایج همین فیلتر مد نظرش بوده -> دوباره همون فیلتر رو اجرا می‌کنیم
        $symbolIds = $validated['symbol_ids'] ?? $this->reapplyFilterForAllResults($request);

        $watchlist->symbols()->syncWithoutDetaching($symbolIds);

        return redirect()
            ->route('symbols.screener', $request->except(['watchlist_id', 'symbol_ids', '_token']))
            ->with('success', count($symbolIds) . " نماد به واچ‌لیست «{$watchlist->name}» اضافه شد.");
    }

    private function reapplyFilterForAllResults(Request $request): array
    {
        $filters = SymbolScreenerFilter::normalizeFilters($request->all());

        $allSymbols = Symbol::with([
                'pivotLevels',
                'technicalSnapshot',
                'livePrice',
                'latestDailyPrice',
            ])
            ->where('is_active', true)
            ->when($request->filled('symbol_group_id'), fn ($q) => $q->where('symbol_group_id', $request->symbol_group_id))
            ->get();

        return app(SymbolScreenerFilter::class)->apply($allSymbols, $filters)->pluck('id')->all();
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
