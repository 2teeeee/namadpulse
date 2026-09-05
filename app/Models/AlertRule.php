<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlertRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'symbol_id',
        'type',
        'condition_value',
        'status',
        'notify_via',
        'cooldown_minutes',
        'triggered_at',
    ];

    protected $casts = [
        'condition_value' => 'decimal:2',
        'notify_via' => 'array',
        'triggered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function symbol(): BelongsTo
    {
        return $this->belongsTo(Symbol::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AlertLog::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /** آیا کول‌داون قانون گذشته و می‌تواند دوباره trigger شود؟ */
    public function isCooldownOver(): bool
    {
        if (! $this->triggered_at) {
            return true;
        }

        return $this->triggered_at->addMinutes($this->cooldown_minutes)->isPast();
    }
}
