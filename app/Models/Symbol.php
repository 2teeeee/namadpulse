<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Symbol extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol_group_id',
        'isin_code',
        'tsetmc_id',
        'ticker',
        'name',
        'name_en',
        'board',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(SymbolGroup::class, 'symbol_group_id');
    }

    public function dailyPrices(): HasMany
    {
        return $this->hasMany(SymbolDailyPrice::class);
    }

    public function livePrice(): HasOne
    {
        return $this->hasOne(SymbolLivePrice::class);
    }

    public function alertRules(): HasMany
    {
        return $this->hasMany(AlertRule::class);
    }

    public function watchlists(): BelongsToMany
    {
        return $this->belongsToMany(Watchlist::class, 'watchlist_symbol')
            ->withPivot('notes')
            ->withTimestamps();
    }

    /** آخرین قیمت پایانی روزانه، برای محاسبه‌ی درصد تغییر لحظه‌ای نسبت به دیروز */
    public function latestDailyPrice(): HasOne
    {
        return $this->hasOne(SymbolDailyPrice::class)->latestOfMany('trade_date');
    }
}
