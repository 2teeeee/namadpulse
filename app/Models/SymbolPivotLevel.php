<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SymbolPivotLevel extends Model
{
    public const MONTH = 'month';
    public const QUARTER = 'quarter';
    public const YEAR = 'year';

    protected $fillable = [
        'symbol_id',
        'period_type',
        'period_label',
        'high',
        'low',
        'close',
        'pp',
        'r1',
        'r2',
        'r3',
        's1',
        's2',
        's3',
        'computed_at',
    ];

    protected $casts = [
        'high' => 'decimal:2',
        'low' => 'decimal:2',
        'close' => 'decimal:2',
        'pp' => 'decimal:2',
        'r1' => 'decimal:2',
        'r2' => 'decimal:2',
        'r3' => 'decimal:2',
        's1' => 'decimal:2',
        's2' => 'decimal:2',
        's3' => 'decimal:2',
        'computed_at' => 'datetime',
    ];

    public function symbol(): BelongsTo
    {
        return $this->belongsTo(Symbol::class);
    }

    /** درصد فاصله‌ی یک سطح نسبت به آخرین قیمت، برای نمایش مثل نمونه‌ی «2,147  21.56 %» */
    public function distancePercentFrom(float $lastPrice, string $level): ?float
    {
        $value = $this->{$level} ?? null;

        if (is_null($value) || (float) $value === 0.0) {
            return null;
        }

        return round((($lastPrice - (float) $value) / (float) $value) * 100, 2);
    }
}
