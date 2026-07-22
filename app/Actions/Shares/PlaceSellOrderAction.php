<?php

namespace App\Actions\Shares;

use App\Enums\Shares\ShareOrderStatus;
use App\Enums\Shares\ShareOrderType;
use App\Events\Shares\ShareOrderPlaced;
use App\Exceptions\Shares\HoldingPeriodNotMetException;
use App\Exceptions\Shares\InsufficientSharesException;
use App\Models\ShareOrder;
use App\Models\User;
use App\Settings\ShareSettings;

class PlaceSellOrderAction
{
    public function handle(User $user, int $quantity): ShareOrder
    {
        $settings = app(ShareSettings::class);

        $totalShares = $user->total_shares;

        if ($totalShares < $quantity) {
            throw new InsufficientSharesException('Insufficient shares.');
        }

        $eligibleShares = $user->shareHoldings()
            ->where('acquired_at', '<=', now()->subDays($settings->holding_period_days))
            ->sum('quantity');

        if ($eligibleShares < $quantity) {
            $closestLot = $user->shareHoldings()
                ->where('acquired_at', '>', now()->subDays($settings->holding_period_days))
                ->orderBy('acquired_at', 'asc')
                ->first();

            throw new HoldingPeriodNotMetException(
                $closestLot ? $closestLot->acquired_at->addDays($settings->holding_period_days) : now()->addDays($settings->holding_period_days)
            );
        }

        $totalAmount = $quantity * $settings->price_per_share;

        $order = ShareOrder::create([
            'user_id' => $user->id,
            'type' => ShareOrderType::Sell,
            'quantity' => $quantity,
            'price_per_share' => $settings->price_per_share,
            'total_amount' => $totalAmount,
            'status' => ShareOrderStatus::Pending,
        ]);

        ShareOrderPlaced::dispatch($order);

        return $order;
    }
}
