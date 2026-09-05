<?php

namespace App\Services\Indicators;

use App\Models\Symbol;

class PivotPointCalculator
{
    /**
     * @return array<string, array{
     *     period_label: string, high: float, low: float, close: float,
     *     pp: float, r1: float, r2: float, r3: float, s1: float, s2: float, s3: float,
     * }|null>
     */
    public function calculate(Symbol $symbol): array
    {
        $latest = $symbol->dailyPrices()->orderByDesc('trade_date')->first();

        if (! $latest) {
            return ['month' => null, 'quarter' => null, 'year' => null];
        }

        [$year, $month] = $this->parseJalali($latest->jalali_date);

        return [
            'month' => $this->buildLevel($symbol, ...$this->previousMonthRange($year, $month)),
            'quarter' => $this->buildLevel($symbol, ...$this->previousQuarterRange($year, $month)),
            'year' => $this->buildLevel($symbol, $year - 1, $year - 1, 1, 12),
        ];
    }

    /** @return array{0:int,1:string} [year, month] از رشته‌ی "1404/06/04" */
    private function parseJalali(string $jalaliDate): array
    {
        [$y, $m] = explode('/', $jalaliDate);

        return [(int) $y, (int) $m];
    }

    /** بازه‌ی ماه قبل: اگر ماه جاری ۱ باشد، ماه ۱۲ سال قبل است */
    private function previousMonthRange(int $year, int $month): array
    {
        return $month === 1
            ? [$year - 1, $year - 1, 12, 12]
            : [$year, $year, $month - 1, $month - 1];
    }

    /** بازه‌ی فصل قبل (فصل‌های شمسی: بهار ۱-۳، تابستان ۴-۶، پاییز ۷-۹، زمستان ۱۰-۱۲) */
    private function previousQuarterRange(int $year, int $month): array
    {
        $currentQuarter = (int) ceil($month / 3);
        $quarterStartMonths = [1, 4, 7, 10];

        if ($currentQuarter === 1) {
            return [$year - 1, $year - 1, 10, 12];
        }

        $prevQuarter = $currentQuarter - 1;
        $startMonth = $quarterStartMonths[$prevQuarter - 1];
        $endMonth = $startMonth + 2;

        return [$year, $year, $startMonth, $endMonth];
    }

    private function buildLevel(Symbol $symbol, int $startYear, int $endYear, int $startMonth, int $endMonth): ?array
    {
        $rows = $symbol->dailyPrices()
            ->where(function ($query) use ($startYear, $endYear, $startMonth, $endMonth) {
                for ($y = $startYear; $y <= $endYear; $y++) {
                    $from = $y === $startYear ? $startMonth : 1;
                    $to = $y === $endYear ? $endMonth : 12;

                    $query->orWhere(function ($q) use ($y, $from, $to) {
                        for ($m = $from; $m <= $to; $m++) {
                            $q->orWhere('jalali_date', 'like', sprintf('%04d/%02d/%%', $y, $m));
                        }
                    });
                }
            })
            ->orderBy('trade_date')
            ->get(['jalali_date', 'high', 'low', 'final', 'trade_date']);

        if ($rows->isEmpty()) {
            return null;
        }

        $high = (float) $rows->max('high');
        $low = (float) $rows->min('low');
        $close = (float) $rows->last()->final;

        $pp = ($high + $low + $close) / 3;

        $label = $startYear === $endYear && $startMonth === $endMonth
            ? sprintf('%04d/%02d', $startYear, $startMonth)
            : sprintf('%04d/%02d–%02d', $startYear, $startMonth, $endMonth);

        return [
            'period_label' => $label,
            'high' => round($high, 2),
            'low' => round($low, 2),
            'close' => round($close, 2),
            'pp' => round($pp, 2),
            'r1' => round((2 * $pp) - $low, 2),
            'r2' => round($pp + ($high - $low), 2),
            'r3' => round($high + 2 * ($pp - $low), 2),
            's1' => round((2 * $pp) - $high, 2),
            's2' => round($pp - ($high - $low), 2),
            's3' => round($low - 2 * ($high - $pp), 2),
        ];
    }
}
