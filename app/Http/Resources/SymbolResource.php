<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SymbolResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lastPrice = $this->livePrice?->last_price ?? $this->latestDailyPrice?->final;
        $changePercent = $this->livePrice?->change_percent;

        return [
            'id' => $this->id,
            'ticker' => $this->ticker,
            'name' => $this->name,
            'group' => $this->whenLoaded('group', fn () => $this->group->name),
            'last_price' => $lastPrice ? (float) $lastPrice : null,
            'change_percent' => ! is_null($changePercent) ? (float) $changePercent : null,
        ];
    }
}
