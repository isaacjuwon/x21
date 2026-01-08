<?php

namespace App\Listeners;

use App\Events\Shares\SharesPurchased;
use App\Events\Shares\SharesSold;
use App\Notifications\Shares\SharesPurchasedNotification;
use App\Notifications\Shares\SharesSoldNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendShareNotifications implements ShouldQueue
{
    public function subscribe($events): array
    {
        return [
            SharesPurchased::class => 'handleSharesPurchased',
            SharesSold::class => 'handleSharesSold',
        ];
    }

    public function handleSharesPurchased(SharesPurchased $event): void
    {
        $event->holder->notify(new SharesPurchasedNotification(
            $event->quantity,
            0, // Price unknown
            0  // Total unknown
        ));
    }

    public function handleSharesSold(SharesSold $event): void
    {
        $event->holder->notify(new SharesSoldNotification(
            $event->quantity,
            0, // Price unknown
            0  // Total unknown
        ));
    }
}
