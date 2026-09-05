<?php

namespace App\Services\Indicators;

use App\Models\Symbol;
use App\Models\SymbolPivotLevel;
use App\Models\SymbolTechnicalSnapshot;
use App\Models\SymbolZigzagPoint;

class IndicatorCalculationService
{
    public function __construct(
        private readonly MovingAverageCalculator $movingAverageCalculator,
        private readonly PivotPointCalculator $pivotPointCalculator,
        private readonly ZigZagCalculator $zigZagCalculator,
    ) {
    }

    public function calculateFor(Symbol $symbol): void
    {
        $this->persistMovingAverages($symbol);
        $this->persistPivotLevels($symbol);
        $this->persistZigZagPoints($symbol);
    }

    private function persistMovingAverages(Symbol $symbol): void
    {
        $values = $this->movingAverageCalculator->calculate($symbol);

        SymbolTechnicalSnapshot::updateOrCreate(
            ['symbol_id' => $symbol->id],
            [...$values, 'computed_at' => now()]
        );
    }

    private function persistPivotLevels(Symbol $symbol): void
    {
        foreach ($this->pivotPointCalculator->calculate($symbol) as $periodType => $level) {
            if (is_null($level)) {
                continue;
            }

            SymbolPivotLevel::updateOrCreate(
                ['symbol_id' => $symbol->id, 'period_type' => $periodType],
                [...$level, 'computed_at' => now()]
            );
        }
    }

    private function persistZigZagPoints(Symbol $symbol): void
    {
        $points = $this->zigZagCalculator->calculate($symbol);

        // بازمحاسبه‌ی کامل: نقاط قبلی حذف و لیست جدید جایگزین می‌شود
        SymbolZigzagPoint::where('symbol_id', $symbol->id)->delete();

        $rows = $points->map(fn (array $point) => [
            'symbol_id' => $symbol->id,
            'point_date' => $point['point_date'],
            'jalali_date' => $point['jalali_date'],
            'price' => $point['price'],
            'type' => $point['type'],
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        if (! empty($rows)) {
            SymbolZigzagPoint::insert($rows);
        }
    }
}
