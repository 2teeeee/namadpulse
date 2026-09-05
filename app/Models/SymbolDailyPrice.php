<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SymbolDailyPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol_id',
        'trade_date',
        'jalali_date',
        'open',
        'high',
        'low',
        'close',
        'final',
        'volume',
        'value',
        'trades_count',
        'is_adjusted',
    ];

    protected $casts = [
        'trade_date' => 'date',
        'open' => 'decimal:2',
        'high' => 'decimal:2',
        'low' => 'decimal:2',
        'close' => 'decimal:2',
        'final' => 'decimal:2',
        'is_adjusted' => 'boolean',
    ];

    public function symbol(): BelongsTo
    {
        return $this->belongsTo(Symbol::class);
    }
}
