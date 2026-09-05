<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SymbolTechnicalSnapshot extends Model
{
    protected $primaryKey = 'symbol_id';
    public $incrementing = false;

    protected $fillable = [
        'symbol_id',
        'price_ma_5',
        'price_ma_20',
        'price_ma_60',
        'volume_ma_5',
        'volume_ma_20',
        'volume_ma_60',
        'ema_100',
        'ema_200',
        'computed_at',
    ];

    protected $casts = [
        'price_ma_5' => 'decimal:2',
        'price_ma_20' => 'decimal:2',
        'price_ma_60' => 'decimal:2',
        'ema_100' => 'decimal:2',
        'ema_200' => 'decimal:2',
        'computed_at' => 'datetime',
    ];

    public function symbol(): BelongsTo
    {
        return $this->belongsTo(Symbol::class);
    }

    /** کراس میانگین‌های قیمت (مثلاً "5-20": true یعنی MA5 بالای MA20 قرار دارد) */
    public function priceCross(int $fast, int $slow): ?bool
    {
        $fastValue = $this->{"price_ma_{$fast}"} ?? null;
        $slowValue = $this->{"price_ma_{$slow}"} ?? null;

        if (is_null($fastValue) || is_null($slowValue)) {
            return null;
        }

        return (float) $fastValue > (float) $slowValue;
    }

    /** کراس میانگین‌های حجم */
    public function volumeCross(int $fast, int $slow): ?bool
    {
        $fastValue = $this->{"volume_ma_{$fast}"} ?? null;
        $slowValue = $this->{"volume_ma_{$slow}"} ?? null;

        if (is_null($fastValue) || is_null($slowValue)) {
            return null;
        }

        return (float) $fastValue > (float) $slowValue;
    }
}
