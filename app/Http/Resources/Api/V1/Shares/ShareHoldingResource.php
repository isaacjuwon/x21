<?php

namespace App\Http\Resources\Api\V1\Shares;

use App\Models\ShareListing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShareHoldingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->resource;
        $totalQuantity = $user->total_shares;
        $oldestAcquiredAt = $user->shareHoldings()->orderBy('acquired_at', 'asc')->value('acquired_at');
        
        $eligibleQuantity = $user->shareHoldings()
            ->where('acquired_at', '<=', now()->subDays(config('shares.holding_period_days')))
            ->sum('quantity');

        return [
            'quantity' => $totalQuantity,
            'acquired_at' => $oldestAcquiredAt,
            'market_value' => $totalQuantity * ShareListing::first()?->price ?? 0,
            'eligible_for_sale' => $eligibleQuantity > 0,
            'eligible_quantity' => $eligibleQuantity,
        ];
    }
}
