<?php

namespace App\Listeners;

use App\Events\FundsTransferred;
use App\Notifications\Transfer\TransferReceivedNotification;
use App\Notifications\Transfer\TransferSentNotification;

class SendTransferNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function handle(FundsTransferred $event): void
    {
        $event->sender->notify(new TransferSentNotification($event->recipient, $event->amount, $event->notes));
        $event->recipient->notify(new TransferReceivedNotification($event->sender, $event->amount, $event->notes));
    }
}
