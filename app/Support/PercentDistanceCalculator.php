<?php

namespace App\Support;

class PercentDistanceCalculator
{
    /**
     * درصد فاصله‌ی یک سطح (پیووت/میانگین/...) تا آخرین قیمت.
     *
     * - اگر سطح پایین‌تر یا برابر آخرین قیمت باشد: چند درصد قیمت از آن سطح بالاتر رفته (نسبت به خود سطح).
     * - اگر سطح بالاتر از آخرین قیمت باشد: چند درصد باید رشد کند تا به آن سطح برسد (نسبت به آخرین قیمت).
     *
     * علامت نتیجه همیشه مثبت است؛ جهت (بالای سطح / زیر سطح) را جداگانه با isAboveLevel() بررسی کنید.
     */
    public static function between(float $level, float $lastPrice): ?float
    {
        if ($level <= 0 || $lastPrice <= 0) {
            return null;
        }

        return $level <= $lastPrice
            ? round((($lastPrice - $level) / $level) * 100, 2)
            : round((($level - $lastPrice) / $lastPrice) * 100, 2);
    }

    public static function isAboveLevel(float $level, float $lastPrice): bool
    {
        return $lastPrice > $level;
    }
}
