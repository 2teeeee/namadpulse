<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'mobile' => $this->mobile,
            'is_active' => $this->is_active,
            'roles' => $this->roles->pluck('name'),
            'is_admin' => $this->isAdmin(),
        ];
    }
}
