<?php

namespace App\Services\Indicators;

use App\Models\Symbol;
use Illuminate\Support\Collection;

class MovingAverageCalculator
{
    /**
     * @return array{
     *     price_ma_5: ?float, price_ma_20: ?float, price_ma_60: ?float,
     *     volume_ma_5: ?int, volume_ma_20: ?int, volume_ma_60: ?int,
     *     ema_100: ?float, ema_200: ?float,
     * }
     */
    public function calculate(Symbol $symbol): array
    {
        // تمام تاریخچه به ترتیب صعودی تاریخ؛ برای EMA به کل سابقه نیاز داریم، نه فقط چند روز آخر
        $rows = $symbol->dailyPrices()
            ->orderBy('trade_date')
            ->get(['final', 'volume']);

        $closes = $rows->pluck('final')->map(fn ($v) => (float) $v);
        $volumes = $rows->pluck('volume')->map(fn ($v) => (float) $v);

        return [
            'price_ma_5' => $this->simpleMovingAverage($closes, 5),
            'price_ma_20' => $this->simpleMovingAverage($closes, 20),
            'price_ma_60' => $this->simpleMovingAverage($closes, 60),

            'volume_ma_5' => $this->simpleMovingAverage($volumes, 5),
            'volume_ma_20' => $this->simpleMovingAverage($volumes, 20),
            'volume_ma_60' => $this->simpleMovingAverage($volumes, 60),

            'ema_100' => $this->exponentialMovingAverage($closes, 100),
            'ema_200' => $this->exponentialMovingAverage($closes, 200),
        ];
    }

    /** میانگین ساده‌ی N روز آخر مجموعه؛ اگر داده کافی نباشد null برمی‌گرداند */
    private function simpleMovingAverage(Collection $values, int $period): ?float
    {
        if ($values->count() < $period) {
            return null;
        }

        return round($values->slice(-$period)->avg(), 2);
    }

    /**
     * میانگین متحرک نمایی N دوره‌ای روی کل سابقه.
     * دوره‌ی seed (میانگین ساده‌ی N مقدار اول) به‌عنوان مقدار شروع EMA در نظر گرفته می‌شود.
     */
    private function exponentialMovingAverage(Collection $values, int $period): ?float
    {
        if ($values->count() < $period) {
            return null;
        }

        $values = $values->values();
        $k = 2 / ($period + 1);

        $ema = $values->slice(0, $period)->avg();

        foreach ($values->slice($period) as $price) {
            $ema = ($price * $k) + ($ema * (1 - $k));
        }

        return round($ema, 2);
    }
}
