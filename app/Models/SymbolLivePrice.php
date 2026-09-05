<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SymbolLivePrice extends Model
{
    use HasFactory;

    // کلید اصلی این جدول symbol_id است، نه id خودکار
    protected $primaryKey = 'symbol_id';
    public $incrementing = false;

    protected $fillable = [
        'symbol_id',
        'last_price',
        'close_price',
        'change_percent',
        'volume',
        'best_buy_price',
        'best_sell_price',
        'fetched_at',
    ];

    protected $casts = [
        'last_price' => 'decimal:2',
        'close_price' => 'decimal:2',
        'change_percent' => 'decimal:2',
        'best_buy_price' => 'decimal:2',
        'best_sell_price' => 'decimal:2',
        'fetched_at' => 'datetime',
    ];

    public function symbol(): BelongsTo
    {
        return $this->belongsTo(Symbol::class);
    }
}
