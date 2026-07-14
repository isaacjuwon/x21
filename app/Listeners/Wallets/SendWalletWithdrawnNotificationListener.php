<?php

namespace App\Listeners\Wallets;

use App\Events\Wallets\WalletWithdrawn;
use App\Notifications\Wallets\WalletWithdrawnNotification;

class SendWalletWithdrawnNotificationListener
{
    public function handle(WalletWithdrawn $event): void
    {
        $user = $event->transaction->performedByUser;

        if (! $user) {
            return;
        }

        $user->notify(new WalletWithdrawnNotification($event->transaction));
    }
}
