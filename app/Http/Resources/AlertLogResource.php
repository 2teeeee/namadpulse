<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlertLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'symbol' => [
                'ticker' => $this->alertRule->symbol->ticker,
                'name' => $this->alertRule->symbol->name,
            ],
            'price_at_trigger' => (float) $this->price_at_trigger,
            'channel' => $this->channel,
            'status' => $this->status,
            'sent_at' => $this->sent_at,
        ];
    }
}
