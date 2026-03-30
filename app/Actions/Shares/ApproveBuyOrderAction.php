<?php

namespace App\Actions\Shares;

use App\Enums\Shares\ShareOrderStatus;
use App\Events\Shares\ShareOrderApproved;
use App\Exceptions\Shares\InvalidShareOrderStateException;
use App\Models\ShareHolding;
use App\Models\ShareListing;
use App\Models\ShareOrder;
use App\Models\User;
use App\Notifications\Shares\ShareOrderApprovedNotification;

class ApproveBuyOrderAction
{
    public function handle(ShareOrder $order, User $actor): ShareOrder
    {
        if ($order->status !== ShareOrderStatus::Pending) {
            throw new InvalidShareOrderStateException('Order is not in a pending state.');
        }

        $order->holdTransaction->confirm();

        $holding = ShareHolding::firstOrCreate(
            ['user_id' => $order->user_id],
            ['quantity' => 0]
        );

        $holding->increment('quantity', $order->quantity);

        if ($holding->wasRecentlyCreated || $holding->acquired_at === null) {
            $holding->update(['acquired_at' => now()]);
        }

        ShareListing::firstOrFail()->decrement('available_shares', $order->quantity);

        $order->update(['status' => ShareOrderStatus::Approved]);

        ShareOrderApproved::dispatch($order);

        $order->user->notify(new ShareOrderApprovedNotification($order));

        return $order->fresh();
    }
}
