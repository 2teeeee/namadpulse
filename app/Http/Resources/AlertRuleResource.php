<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlertRuleResource extends JsonResource
{
    private const TYPE_LABELS = [
        'price_above' => 'قیمت بالاتر از',
        'price_below' => 'قیمت پایین‌تر از',
        'percent_change_up' => 'رشد درصدی',
        'percent_change_down' => 'افت درصدی',
        'volume_spike' => 'جهش حجم',
    ];

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'symbol' => [
                'id' => $this->symbol->id,
                'ticker' => $this->symbol->ticker,
                'name' => $this->symbol->name,
            ],
            'type' => $this->type,
            'type_label' => self::TYPE_LABELS[$this->type] ?? $this->type,
            'condition_value' => (float) $this->condition_value,
            'status' => $this->status,
            'notify_via' => $this->notify_via,
            'cooldown_minutes' => $this->cooldown_minutes,
            'triggered_at' => $this->triggered_at,
        ];
    }
}
