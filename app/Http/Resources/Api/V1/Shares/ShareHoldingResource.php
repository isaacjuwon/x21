<?php

namespace App\Http\Resources\Api\V1\Shares;

use App\Models\User;
use App\Settings\ShareSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShareHoldingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;

        $settings = app(ShareSettings::class);
        $totalQuantity = (int) $user->shareHoldings()->sum('quantity');
        $currentValue = $totalQuantity * $settings->price_per_share;

        $eligibleQuantity = (int) $user->shareHoldings()
            ->where('acquired_at', '<=', now()->subDays($settings->holding_period_days))
            ->sum('quantity');

        $oldestAcquiredAt = $user->shareHoldings()->orderBy('acquired_at', 'asc')->value('acquired_at');

        return [
            'total_shares' => $totalQuantity,
            'current_value' => $currentValue,
            'price_per_share' => $settings->price_per_share,
            'eligible_for_sale' => $eligibleQuantity > 0,
            'eligible_quantity' => $eligibleQuantity,
            'oldest_acquired_at' => $oldestAcquiredAt,
        ];
    }
}
