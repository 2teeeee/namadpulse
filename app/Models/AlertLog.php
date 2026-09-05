<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'alert_rule_id',
        'price_at_trigger',
        'channel',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'price_at_trigger' => 'decimal:2',
        'sent_at' => 'datetime',
    ];

    public function alertRule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class);
    }
}
