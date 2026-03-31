<?php

namespace App\Http\Resources\Api\V1\Wallet;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'balance' => $this->balance,
            'held_balance' => $this->held_balance,
            'available_balance' => $this->available_balance,
            'updated_at' => $this->updated_at,
        ];
    }
}
