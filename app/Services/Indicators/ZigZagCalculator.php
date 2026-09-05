<?php

namespace App\Services\Indicators;

use App\Models\Symbol;
use Illuminate\Support\Collection;

class ZigZagCalculator
{
    public function __construct(
        private readonly float $thresholdPercent = 0,
    ) {
    }

    /**
     * @return Collection<int, array{point_date: \Illuminate\Support\Carbon, jalali_date: string, price: float, type: string}>
     */
    public function calculate(Symbol $symbol): Collection
    {
        $threshold = $this->thresholdPercent ?: (float) config('indicators.zigzag_threshold_percent');

        $latest = $symbol->dailyPrices()->orderByDesc('trade_date')->first();

        if (! $latest) {
            return collect();
        }

        [$currentYear] = explode('/', $latest->jalali_date);
        $startOfPreviousYear = sprintf('%04d/01/01', ((int) $currentYear) - 1);

        $rows = $symbol->dailyPrices()
            ->where('jalali_date', '>=', $startOfPreviousYear)
            ->orderBy('trade_date')
            ->get(['trade_date', 'jalali_date', 'final']);

        if ($rows->count() < 2) {
            return collect();
        }

        return $this->findSwingPoints($rows, $threshold);
    }

    private function findSwingPoints(Collection $rows, float $threshold): Collection
    {
        $rows = $rows->values();
        $n = $rows->count();

        // مرحله‌ی اول: پیدا کردن جهت حرکت اولیه (اولین نوسان بزرگ‌تر از آستانه)
        $basePrice = (float) $rows[0]->final;
        $trend = null;

        for ($i = 1; $i < $n; $i++) {
            $change = (((float) $rows[$i]->final - $basePrice) / $basePrice) * 100;

            if ($change >= $threshold) {
                $trend = 'up';
                break;
            }

            if ($change <= -$threshold) {
                $trend = 'down';
                break;
            }
        }

        if (is_null($trend)) {
            return collect(); // در کل بازه هیچ نوسان ماژوری اتفاق نیفتاده
        }

        $pivots = collect([
            $this->point($rows[0], $trend === 'up' ? 'trough' : 'peak'),
        ]);

        $extremeIndex = 0;
        $extremePrice = $basePrice;

        for ($i = 1; $i < $n; $i++) {
            $price = (float) $rows[$i]->final;

            if ($trend === 'up') {
                if ($price > $extremePrice) {
                    $extremeIndex = $i;
                    $extremePrice = $price;
                    continue;
                }

                $drop = (($extremePrice - $price) / $extremePrice) * 100;

                if ($drop >= $threshold) {
                    $pivots->push($this->point($rows[$extremeIndex], 'peak'));
                    $trend = 'down';
                    $extremeIndex = $i;
                    $extremePrice = $price;
                }
            } else {
                if ($price < $extremePrice) {
                    $extremeIndex = $i;
                    $extremePrice = $price;
                    continue;
                }

                $rise = (($price - $extremePrice) / $extremePrice) * 100;

                if ($rise >= $threshold) {
                    $pivots->push($this->point($rows[$extremeIndex], 'trough'));
                    $trend = 'up';
                    $extremeIndex = $i;
                    $extremePrice = $price;
                }
            }
        }

        // آخرین اکسترمم (هنوز با نوسان بعدی تایید نشده) هم به‌عنوان نقطه‌ی جاری اضافه می‌شود
        $pivots->push($this->point($rows[$extremeIndex], $trend === 'up' ? 'peak' : 'trough'));

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
