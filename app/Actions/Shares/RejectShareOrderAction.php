<?php

namespace App\Actions\Shares;

use App\Enums\Shares\ShareOrderStatus;
use App\Enums\Shares\ShareOrderType;
use App\Events\Shares\ShareOrderRejected;
use App\Exceptions\Shares\InvalidShareOrderStateException;
use App\Models\ShareOrder;
use App\Models\User;
use App\Notifications\Shares\ShareOrderRejectedNotification;

class RejectShareOrderAction
{
    public function handle(ShareOrder $order, User $actor, string $rejectionReason): ShareOrder
    {
        if ($order->status !== ShareOrderStatus::Pending) {
            throw new InvalidShareOrderStateException('Order is not in a pending state.');
        }

        if ($order->type === ShareOrderType::Buy) {
            $order->holdTransaction->void();
        }

        $order->update(['status' => ShareOrderStatus::Rejected, 'rejection_reason' => $rejectionReason]);

        ShareOrderRejected::dispatch($order);

        $order->user->notify(new ShareOrderRejectedNotification($order));

        return $order->fresh();
    }
}
