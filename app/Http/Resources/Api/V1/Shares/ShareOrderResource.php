<?php

namespace App\Http\Resources\Api\V1\Shares;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShareOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'type' => $this->type->value,
            'quantity' => $this->quantity,
            'price_per_share' => $this->price_per_share,
            'total_amount' => $this->total_amount,
            'status' => $this->status->value,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at,
        ];
    }
}
