<?php

namespace App\Actions\Shares;

use App\Enums\Shares\ShareOrderStatus;
use App\Enums\Shares\ShareOrderType;
use App\Enums\Wallets\WalletType;
use App\Events\Shares\ShareOrderPlaced;
use App\Exceptions\Shares\InsufficientAvailableSharesException;
use App\Exceptions\Shares\MaxSharesPerUserExceededException;
use App\Exceptions\Shares\MinSharesPurchaseNotMetException;
use App\Models\ShareListing;
use App\Models\ShareOrder;
use App\Models\User;
use App\Settings\ShareSettings;

class PlaceBuyOrderAction
{
    public function handle(User $user, int $quantity): ShareOrder
    {
        $settings = app(ShareSettings::class);
        $listing = ShareListing::firstOrFail();

        if ($quantity < $settings->min_shares_purchase) {
            throw new MinSharesPurchaseNotMetException($settings->min_shares_purchase);
        }

        $currentHolding = $user->shareHolding?->quantity ?? 0;
        $pendingBuyQty = $user->shareOrders()
            ->where('type', ShareOrderType::Buy)
            ->where('status', ShareOrderStatus::Pending)
            ->sum('quantity');

        if (($currentHolding + $pendingBuyQty + $quantity) > $settings->max_shares_per_user) {
            throw new MaxSharesPerUserExceededException($settings->max_shares_per_user);
        }

        if ($quantity > $listing->available_shares) {
            throw new InsufficientAvailableSharesException('Insufficient available shares.');
        }

        $totalCost = $quantity * $settings->price_per_share;

        // Debit wallet immediately — no hold, user is charged on placement
        $withdrawTransaction = $user->withdraw($totalCost, WalletType::General, "Share purchase: {$quantity} shares @ {$settings->price_per_share}");

        $order = ShareOrder::create([
            'user_id' => $user->id,
            'type' => ShareOrderType::Buy,
            'quantity' => $quantity,
            'price_per_share' => $settings->price_per_share,
            'total_amount' => $totalCost,
            'status' => ShareOrderStatus::Pending,
            'hold_transaction_id' => $withdrawTransaction->id, // reusing column to store the debit transaction
        ]);

        ShareOrderPlaced::dispatch($order);

        return $order;
    }
}
