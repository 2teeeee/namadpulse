<?php

namespace App\Services\Indicators;

use Illuminate\Support\Collection;

class MacdCalculator
{
    /**
     * محاسبه‌ی خط MACD به‌ازای هر روز از تاریخچه (نه فقط عدد نهایی).
     * خروجی: آرایه‌ی هم‌طول ورودی؛ ایندکس‌هایی که هنوز EMA کند (slow) تعریف نشده، null هستند.
     *
     * @param  Collection<int, float>  $closes  قیمت‌ها به ترتیب صعودی تاریخ
     * @return array<int, float|null>
     */
    public function calculateSeries(Collection $closes, int $fastPeriod = 12, int $slowPeriod = 26): array
    {
        $closes = $closes->values();

        $emaFast = $this->emaSeries($closes, $fastPeriod);
        $emaSlow = $this->emaSeries($closes, $slowPeriod);

        $macd = [];

        foreach ($closes as $i => $close) {
            $macd[$i] = (isset($emaFast[$i]) && isset($emaSlow[$i]))
                ? round($emaFast[$i] - $emaSlow[$i], 4)
                : null;
        }

        return $macd;
    }

    /**
     * سری کامل EMA؛ ایندکس‌های قبل از (period - 1) تعریف نشده‌اند (null).
     * مقدار seed در ایندکس (period - 1) میانگین ساده‌ی همان تعداد روز اول است.
     *
     * @return array<int, float>
     */
    private function emaSeries(Collection $values, int $period): array
    {
        if ($values->count() < $period) {
            return [];
        }

        $k = 2 / ($period + 1);
        $series = [];

        $seed = $values->slice(0, $period)->avg();
        $series[$period - 1] = $seed;
        $prev = $seed;

        for ($i = $period; $i < $values->count(); $i++) {
            $prev = ($values[$i] * $k) + ($prev * (1 - $k));
            $series[$i] = $prev;
        }

        return $series;
    }
}
