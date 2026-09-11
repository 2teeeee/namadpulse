<?php

namespace App\Http\Controllers;

use App\Models\Symbol;
use App\Models\SymbolGroup;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SymbolController extends Controller
{
    public function index(): View
    {
        $symbols = Symbol::with('group', 'livePrice')
            ->where('is_active', true)
            ->orderBy('ticker')
            ->paginate(20);

        return view('symbols.index', compact('symbols'));
    }

    public function show(Symbol $symbol): View
    {
        $symbol->load('group', 'livePrice', 'latestDailyPrice', 'technicalSnapshot');

        $pivotLevels = $symbol->pivotLevels()->get()->keyBy('period_type');

        $zigzagPoints = $symbol->zigzagPoints()->orderBy('point_date')->get();

        $lastPrice = (float) ($symbol->livePrice?->last_price ?? $symbol->latestDailyPrice?->final ?? 0);

        return view('symbols.show', compact('symbol', 'pivotLevels', 'zigzagPoints', 'lastPrice'));
    }

    public function pivots(Request $request): View
    {
        $period = $request->get('period', 'year');
        abort_unless(in_array($period, ['year', 'quarter', 'month'], true), 404);

        $symbols = Symbol::with([
            'pivotLevels' => fn ($query) => $query->where('period_type', $period),
            'livePrice',
            'latestDailyPrice',
        ])
            ->where('is_active', true)
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('ticker', 'like', "%{$request->q}%")
                        ->orWhere('name', 'like', "%{$request->q}%");
                });
            })
            ->when($request->filled('symbol_group_id'), function ($query) use ($request) {
                $query->where('symbol_group_id', $request->symbol_group_id);
            })
            ->orderBy('symbol_group_id')
            ->paginate(100)
            ->withQueryString();

        $symbolGroups = SymbolGroup::orderBy('name')->get();

        return view('symbols.pivots', compact('symbols', 'period', 'symbolGroups'));
    }
}
