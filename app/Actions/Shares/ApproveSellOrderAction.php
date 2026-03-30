<?php

namespace App\Actions\Shares;

use App\Enums\Shares\ShareOrderStatus;
use App\Enums\Wallets\WalletType;
use App\Events\Shares\ShareOrderApproved;
use App\Exceptions\Shares\InvalidShareOrderStateException;
use App\Models\ShareListing;
use App\Models\ShareOrder;
use App\Models\User;
use App\Notifications\Shares\ShareOrderApprovedNotification;

class ApproveSellOrderAction
{
    public function handle(ShareOrder $order, User $actor): ShareOrder
    {
        if ($order->status !== ShareOrderStatus::Pending) {
            throw new InvalidShareOrderStateException('Order is not in a pending state.');
        }

        $order->user->deposit($order->total_amount, WalletType::General);

        $order->user->shareHolding->decrement('quantity', $order->quantity);

        ShareListing::firstOrFail()->increment('available_shares', $order->quantity);

        $order->update(['status' => ShareOrderStatus::Approved]);

        ShareOrderApproved::dispatch($order);

        $order->user->notify(new ShareOrderApprovedNotification($order));

        return $order->fresh();
    }
}
