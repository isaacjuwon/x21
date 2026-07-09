<?php

namespace App\Listeners\Services;

use App\Events\Services\ServicePurchased;
use App\Notifications\Services\ServicePurchasedNotification;

class SendServicePurchasedNotificationListener
{
    public function handle(ServicePurchased $event): void
    {
        $user = $event->transaction->user;

        if (! $user) {
            return;
        }

        $user->notify(new ServicePurchasedNotification($event->transaction));
    }
}
