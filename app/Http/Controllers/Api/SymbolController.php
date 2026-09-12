<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SymbolResource;
use App\Models\Symbol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SymbolController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $symbols = Symbol::with('group', 'livePrice')
            ->where('is_active', true)
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('ticker', 'like', "%{$request->q}%")
                        ->orWhere('name', 'like', "%{$request->q}%");
                });
            })
            ->orderBy('symbol_group_id')
            ->paginate(20);

        return response()->json([
            'data' => SymbolResource::collection($symbols->items()),
            'meta' => [
                'current_page' => $symbols->currentPage(),
                'last_page' => $symbols->lastPage(),
                'total' => $symbols->total(),
            ],
        ]);
    }

    public function show(Symbol $symbol): JsonResponse
    {
        $symbol->load('group', 'livePrice', 'latestDailyPrice', 'technicalSnapshot');

        $pivotLevels = $symbol->pivotLevels()->get()->keyBy('period_type');
        $zigzagPoints = $symbol->zigzagPoints()->orderBy('point_date')->get();

        $lastPrice = (float) ($symbol->livePrice?->last_price ?? $symbol->latestDailyPrice?->final ?? 0);
        $snapshot = $symbol->technicalSnapshot;

        return response()->json([
            'data' => [
                'id' => $symbol->id,
                'ticker' => $symbol->ticker,
                'name' => $symbol->name,
                'group' => $symbol->group->name,
                'last_price' => $lastPrice,
                'capital' => $symbol->capital(),
                'market_cap' => $symbol->marketCap(),

                'averages' => $this->buildAverages($snapshot, $lastPrice),

                'flags' => [
                    'above_yearly_pivot' => $pivotLevels->get('year')?->isAboveLevel($lastPrice, 'pp'),
                    'above_ema_200' => $snapshot?->isAboveField('ema_200', $lastPrice),
                ],

                'price_crosses' => $this->buildCrosses($snapshot, 'priceCross'),
                'volume_crosses' => $this->buildCrosses($snapshot, 'volumeCross'),

                'pivot_levels' => $this->buildPivotLevels($pivotLevels, $lastPrice),

                'zigzag_points' => $zigzagPoints->map(fn ($point) => [
                    'jalali_date' => $point->jalali_date,
                    'price' => (float) $point->price,
                    'type' => $point->type,
                    'percent' => \App\Support\PercentDistanceCalculator::between((float) $point->price, $lastPrice),
                    'is_above' => \App\Support\PercentDistanceCalculator::isAboveLevel((float) $point->price, $lastPrice),
                ]),
            ],
        ]);
    }

    private function buildAverages(?\App\Models\SymbolTechnicalSnapshot $snapshot, float $lastPrice): array
    {
        $fields = ['ma_20' => 'price_ma_20', 'ema_100' => 'ema_100', 'ema_200' => 'ema_200'];
        $result = [];

        foreach ($fields as $key => $field) {
            $value = $snapshot?->{$field};

            $result[$key] = is_null($value) ? null : [
                'value' => (float) $value,
                'percent' => $snapshot->percentVsLast($field, $lastPrice),
                'is_above' => $snapshot->isAboveField($field, $lastPrice),
            ];
        }

        return $result;
    }

    private function buildCrosses(?\App\Models\SymbolTechnicalSnapshot $snapshot, string $method): array
    {
        $pairs = ['5_20' => [5, 20], '5_60' => [5, 60], '20_60' => [20, 60]];
        $result = [];

        foreach ($pairs as $key => [$fast, $slow]) {
            $result[$key] = $snapshot?->{$method}($fast, $slow);
        }

        return $result;
    }

    private function buildPivotLevels($pivotLevels, float $lastPrice): array
    {
        $levels = ['pp', 'r1', 'r2', 'r3', 's1', 's2', 's3'];
        $result = [];

        foreach (['year', 'quarter', 'month'] as $period) {
            $level = $pivotLevels->get($period);

            if (! $level) {
                $result[$period] = null;

                continue;
            }

            $levelData = [];
            foreach ($levels as $key) {
                $levelData[$key] = [
                    'value' => (float) $level->{$key},
                    'percent' => $level->distancePercentFrom($lastPrice, $key),
                    'is_above' => $level->isAboveLevel($lastPrice, $key),
                ];
            }

            $result[$period] = [
                'high' => (float) $level->high,
                'low' => (float) $level->low,
                'close' => (float) $level->close,
                'levels' => $levelData,
            ];
        }

        return $result;
    }

    public function pivots(Request $request): JsonResponse
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
            ->paginate(25);

        $levels = ['pp', 'r1', 'r2', 'r3', 's1', 's2', 's3'];

        $data = collect($symbols->items())->map(function (Symbol $symbol) use ($levels) {
            $level = $symbol->pivotLevels->first();
            $lastPrice = (float) ($symbol->livePrice?->last_price ?? $symbol->latestDailyPrice?->final ?? 0);

            $levelData = null;
            if ($level && $lastPrice > 0) {
                $levelData = [];
                foreach ($levels as $key) {
                    $levelData[$key] = [
                        'value' => (float) $level->{$key},
                        'percent' => $level->distancePercentFrom($lastPrice, $key),
                        'is_above' => $level->isAboveLevel($lastPrice, $key),
                    ];
                }
            }

            return [
                'id' => $symbol->id,
                'ticker' => $symbol->ticker,
                'name' => $symbol->name,
                'levels' => $levelData,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $symbols->currentPage(),
                'last_page' => $symbols->lastPage(),
                'total' => $symbols->total(),
            ],
        ]);
    }
}
