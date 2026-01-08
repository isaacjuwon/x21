<?php

namespace App\Listeners;

use App\Events\Wallet\WalletCredited;
use App\Events\Wallet\WalletDebited;
use App\Events\Wallet\WalletTransferred;
use App\Notifications\Wallet\WalletCreditedNotification;
use App\Notifications\Wallet\WalletDebitedNotification;
use App\Notifications\Wallet\TransferSentNotification;
use App\Notifications\Wallet\TransferReceivedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWalletNotifications implements ShouldQueue
{
    public function subscribe($events): array
    {
        return [
            WalletCredited::class => 'handleWalletCredited',
            WalletDebited::class => 'handleWalletDebited',
            WalletTransferred::class => 'handleWalletTransferred',
        ];
    }

    public function handleWalletCredited(WalletCredited $event): void
    {
        $event->user->notify(new WalletCreditedNotification(
            $event->amount,
            $event->type,
            $event->notes
        ));
    }

    public function handleWalletDebited(WalletDebited $event): void
    {
        $event->user->notify(new WalletDebitedNotification(
            $event->amount,
            $event->type,
            $event->notes
        ));
    }

    public function handleWalletTransferred(WalletTransferred $event): void
    {
        $event->sender->notify(new TransferSentNotification(
            $event->recipient,
            $event->amount,
            $event->type,
            $event->notes
        ));

        $event->recipient->notify(new TransferReceivedNotification(
            $event->sender,
            $event->amount,
            $event->type,
            $event->notes
        ));
    }
}
