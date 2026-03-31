<?php

namespace App\Http\Resources\Api\V1\Shares;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DividendPayoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'created_at' => $this->created_at,
            'dividend' => $this->whenLoaded('dividend', fn () => [
                'total_amount' => $this->dividend->total_amount,
                'declared_at' => $this->dividend->declared_at,
            ], fn () => $this->dividend_id ? [
                'total_amount' => $this->dividend?->total_amount,
                'declared_at' => $this->dividend?->declared_at,
            ] : null),
        ];
    }
}
