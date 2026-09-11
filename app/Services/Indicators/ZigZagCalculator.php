<?php

namespace App\Services\Indicators;

use App\Models\Symbol;
use Illuminate\Support\Collection;

class ZigZagCalculator
{
    public function __construct(
        private readonly MacdCalculator $macdCalculator = new MacdCalculator(),
    ) {
    }

    /**
     * نقاط ماژور بر اساس رژیم MACD:
     * - وقتی MACD مثبت است، بالاترین قیمت آن بازه کاندیدای «سقف» است.
     * - وقتی MACD منفی است، پایین‌ترین قیمت آن بازه کاندیدای «کف» است.
     * - نقطه با تغییر علامت MACD (مثبت↔منفی) تایید و ثبت می‌شود.
     *
     * @return Collection<int, array{point_date: \Illuminate\Support\Carbon, jalali_date: string, price: float, type: string}>
     */
    public function calculate(Symbol $symbol): Collection
    {
        // کل تاریخچه برای EMA/MACD دقیق لازم است (نه فقط بازه‌ی نمایش)
        $rows = $symbol->dailyPrices()->orderBy('trade_date')->get(['trade_date', 'jalali_date', 'final']);

        if ($rows->count() < 30) {
            return collect(); // داده‌ی کافی برای MACD(12,26) وجود ندارد
        }

        [$currentYear] = explode('/', $rows->last()->jalali_date);
        $startOfPreviousYear = sprintf('%04d/01/01', ((int) $currentYear) - 1);

        $closes = $rows->pluck('final')->map(fn ($v) => (float) $v);
        $macdSeries = $this->macdCalculator->calculateSeries($closes);

        $startIndex = null;
        foreach ($macdSeries as $i => $value) {
            if (! is_null($value)) {
                $startIndex = $i;
                break;
            }
        }

        if (is_null($startIndex)) {
            return collect();
        }

        $pivots = $this->findRegimePivots($rows, $closes, $macdSeries, $startIndex);

        // فقط نقاطی که از ابتدای سال قبل به بعد هستند نمایش داده می‌شوند
        // (ولی رژیم MACD از قبل از آن بازه هم برای دقت بیشتر در نظر گرفته شده)
        return $pivots->filter(fn (array $p) => $p['jalali_date'] >= $startOfPreviousYear)->values();
    }

    private function findRegimePivots(Collection $rows, Collection $closes, array $macdSeries, int $startIndex): Collection
    {
        $pivots = collect();

        $regime = $macdSeries[$startIndex] >= 0 ? 'positive' : 'negative';
        $extremeIndex = $startIndex;
        $extremePrice = $closes[$startIndex];

        for ($i = $startIndex + 1; $i < $rows->count(); $i++) {
            $macd = $macdSeries[$i] ?? null;

            if (is_null($macd)) {
                continue;
            }

            $price = $closes[$i];
            $currentRegime = $macd >= 0 ? 'positive' : 'negative';

            if ($currentRegime === $regime) {
                if ($regime === 'positive' && $price > $extremePrice) {
                    $extremeIndex = $i;
                    $extremePrice = $price;
                } elseif ($regime === 'negative' && $price < $extremePrice) {
                    $extremeIndex = $i;
                    $extremePrice = $price;
                }

                continue;
            }

            // تغییر علامت MACD -> تایید اکسترمم رژیم قبلی به‌عنوان نقطه ماژور
            $pivots->push($this->point($rows[$extremeIndex], $regime === 'positive' ? 'peak' : 'trough'));

            $regime = $currentRegime;
            $extremeIndex = $i;
            $extremePrice = $price;
        }

        // اکسترمم رژیم جاری هنوز با تغییر علامت تایید نشده، ولی به‌عنوان نقطه‌ی تازه نمایش داده می‌شود
        $pivots->push($this->point($rows[$extremeIndex], $regime === 'positive' ? 'peak' : 'trough'));

        return $pivots;
    }

    private function point($row, string $type): array
    {
        return [
            'point_date' => $row->trade_date,
            'jalali_date' => $row->jalali_date,
            'price' => round((float) $row->final, 2),
            'type' => $type,
        ];
    }
}