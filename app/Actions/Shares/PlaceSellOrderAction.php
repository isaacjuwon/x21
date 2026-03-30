<?php

namespace App\Actions\Shares;

use App\Enums\Shares\ShareOrderStatus;
use App\Enums\Shares\ShareOrderType;
use App\Events\Shares\ShareOrderPlaced;
use App\Exceptions\Shares\HoldingPeriodNotMetException;
use App\Exceptions\Shares\InsufficientSharesException;
use App\Models\ShareListing;
use App\Models\ShareOrder;
use App\Models\User;
use App\Settings\ShareSettings;

class PlaceSellOrderAction
{
    public function handle(User $user, int $quantity): ShareOrder
    {
        $settings = app(ShareSettings::class);
        $listing = ShareListing::firstOrFail();

        $holding = $user->shareHolding;

        if (! $holding || $holding->quantity < $quantity) {
            throw new InsufficientSharesException('Insufficient shares.');
        }

        if ($holding->acquired_at > now()->subDays($settings->holding_period_days)) {
            throw new HoldingPeriodNotMetException(
                $holding->acquired_at->addDays($settings->holding_period_days)
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
