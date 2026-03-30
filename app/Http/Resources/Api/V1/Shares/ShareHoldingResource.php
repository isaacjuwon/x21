<?php

namespace App\Http\Resources\Api\V1\Shares;

use App\Models\ShareListing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShareHoldingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'quantity' => $this->quantity,
            'acquired_at' => $this->acquired_at,
            'market_value' => $this->quantity * ShareListing::first()?->price ?? 0,
            'eligible_for_sale' => $this->acquired_at !== null && $this->acquired_at <= now()->subDays(config('shares.holding_period_days')),
        ];
    }
}
