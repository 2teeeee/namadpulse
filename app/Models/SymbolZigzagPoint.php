<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SymbolZigzagPoint extends Model
{
    public const PEAK = 'peak';
    public const TROUGH = 'trough';

    protected $fillable = [
        'symbol_id',
        'point_date',
        'jalali_date',
        'price',
        'type',
    ];

    protected $casts = [
        'point_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function symbol(): BelongsTo
    {
        return $this->belongsTo(Symbol::class);
    }
}
