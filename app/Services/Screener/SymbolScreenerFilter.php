<?php

namespace App\Services\Screener;

use App\Models\Symbol;
use Illuminate\Support\Collection;

class SymbolScreenerFilter
{
    private const PIVOT_LEVELS = ['pp', 'r1', 'r2', 'r3', 's1', 's2', 's3'];
    private const AVERAGE_FIELDS = ['ma_20' => 'price_ma_20', 'ema_100' => 'ema_100', 'ema_200' => 'ema_200'];
    private const CROSS_PAIRS = [
        '5_20' => [5, 20],
        '5_60' => [5, 60],
        '20_60' => [20, 60],
    ];

    /**
     * @param  Collection<int, Symbol>  $symbols  نمادهایی که از قبل pivotLevels (فیلترشده روی period انتخابی)،
     *                                             technicalSnapshot، livePrice و latestDailyPrice روی‌شان eager-load شده
     * @param  array  $filters  خروجی متد normalizeFilters()
     */
    public function apply(Collection $symbols, array $filters): Collection
    {
        $hasAnyFilter = $this->hasAnyActiveFilter($filters);

        return $symbols->filter(function (Symbol $symbol) use ($filters, $hasAnyFilter) {
            if (! $hasAnyFilter) {
                return true;
            }

            $lastPrice = (float) ($symbol->livePrice?->last_price ?? $symbol->latestDailyPrice?->final ?? 0);

            if ($lastPrice <= 0) {
                return false; // بدون قیمت، هیچ شرطی قابل بررسی نیست
            }

            return $this->matchesPivotFilters($symbol, $filters, $lastPrice)
                && $this->matchesAverageFilters($symbol, $filters, $lastPrice)
                && $this->matchesCrossFilters($symbol, $filters, 'price_cross', fn ($snap, $f, $s) => $snap->priceCross($f, $s))
                && $this->matchesCrossFilters($symbol, $filters, 'volume_cross', fn ($snap, $f, $s) => $snap->volumeCross($f, $s));
        })->values();
    }

    private function matchesPivotFilters(Symbol $symbol, array $filters, float $lastPrice): bool
    {
        $level = $symbol->pivotLevels->first(); // از قبل روی period انتخابی فیلتر شده

        foreach (self::PIVOT_LEVELS as $key) {
            $want = $filters['pivot'][$key] ?? 'any';

            if ($want === 'any') {
                continue;
            }

            if (! $level) {
                return false; // برای این نماد پیووت این بازه محاسبه نشده
            }

            $isAbove = $level->isAboveLevel($lastPrice, $key);

            if ($want === 'above' && ! $isAbove) {
                return false;
            }

            if ($want === 'below' && $isAbove) {
                return false;
            }
        }

        return true;
    }

    private function matchesAverageFilters(Symbol $symbol, array $filters, float $lastPrice): bool
    {
        $snapshot = $symbol->technicalSnapshot;

        foreach (self::AVERAGE_FIELDS as $key => $field) {
            $want = $filters['average'][$key] ?? 'any';

            if ($want === 'any') {
                continue;
            }

            if (! $snapshot || is_null($snapshot->{$field})) {
                return false;
            }

            $isAbove = $snapshot->isAboveField($field, $lastPrice);

            if ($want === 'above' && ! $isAbove) {
                return false;
            }

            if ($want === 'below' && $isAbove) {
                return false;
            }
        }

        return true;
    }

    private function matchesCrossFilters(Symbol $symbol, array $filters, string $group, callable $resolver): bool
    {
        $snapshot = $symbol->technicalSnapshot;

        foreach (self::CROSS_PAIRS as $key => [$fast, $slow]) {
            $want = $filters[$group][$key] ?? 'any';

            if ($want === 'any') {
                continue;
            }

            if (! $snapshot) {
                return false;
            }

            $isBullish = $resolver($snapshot, $fast, $slow);

            if (is_null($isBullish)) {
                return false;
            }

            if ($want === 'bullish' && ! $isBullish) {
                return false;
            }

            if ($want === 'bearish' && $isBullish) {
                return false;
            }
        }

        return true;
    }

    private function hasAnyActiveFilter(array $filters): bool
    {
        foreach (['pivot', 'average', 'price_cross', 'volume_cross'] as $group) {
            foreach (($filters[$group] ?? []) as $value) {
                if ($value !== 'any') {
                    return true;
                }
            }
        }

        return false;
    }

    /** تبدیل ورودی خام request به ساختار یکدست فیلترها */
    public static function normalizeFilters(array $input): array
    {
        $pickThreeState = fn (?string $value, array $allowed) => in_array($value, $allowed, true) ? $value : 'any';

        $pivot = [];
        foreach (self::PIVOT_LEVELS as $key) {
            $pivot[$key] = $pickThreeState($input['pivot'][$key] ?? null, ['above', 'below']);
        }

        $average = [];
        foreach (array_keys(self::AVERAGE_FIELDS) as $key) {
            $average[$key] = $pickThreeState($input['average'][$key] ?? null, ['above', 'below']);
        }

        $priceCross = [];
        $volumeCross = [];
        foreach (array_keys(self::CROSS_PAIRS) as $key) {
            $priceCross[$key] = $pickThreeState($input['price_cross'][$key] ?? null, ['bullish', 'bearish']);
            $volumeCross[$key] = $pickThreeState($input['volume_cross'][$key] ?? null, ['bullish', 'bearish']);
        }

        return [
            'pivot' => $pivot,
            'average' => $average,
            'price_cross' => $priceCross,
            'volume_cross' => $volumeCross,
        ];
    }
}
