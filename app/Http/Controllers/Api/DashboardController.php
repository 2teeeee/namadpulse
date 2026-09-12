<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SymbolResource;
use App\Models\Symbol;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $activeSymbolsCount = Symbol::where('is_active', true)->count();

        $topGainers = Symbol::with('group', 'livePrice')
            ->whereHas('livePrice', fn ($q) => $q->whereNotNull('change_percent'))
            ->join('symbol_live_prices', 'symbols.id', '=', 'symbol_live_prices.symbol_id')
            ->orderByDesc('symbol_live_prices.change_percent')
            ->select('symbols.*')
            ->limit(5)
            ->get();

        $topLosers = Symbol::with('group', 'livePrice')
            ->whereHas('livePrice', fn ($q) => $q->whereNotNull('change_percent'))
            ->join('symbol_live_prices', 'symbols.id', '=', 'symbol_live_prices.symbol_id')
            ->orderBy('symbol_live_prices.change_percent')
            ->select('symbols.*')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => [
                'active_symbols_count' => $activeSymbolsCount,
                'top_gainers' => SymbolResource::collection($topGainers),
                'top_losers' => SymbolResource::collection($topLosers),
            ],
        ]);
    }
}
