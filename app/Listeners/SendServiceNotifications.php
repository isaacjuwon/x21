<?php

namespace App\Listeners;

use App\Events\Services\ServicePurchased;
use App\Notifications\Services\ServicePurchasedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendServiceNotifications implements ShouldQueue
{
    public function subscribe($events): array
    {
        return [
            ServicePurchased::class => 'handleServicePurchased',
        ];
    }

    public function handleServicePurchased(ServicePurchased $event): void
    {
        $transactionUrl = $event->transactionId 
            ? route('transactions.show', $event->transactionId)
            : null;

        $event->user->notify(new ServicePurchasedNotification(
            $event->serviceType,
            $event->productName,
            $event->amount,
            $event->transactionReference,
            $transactionUrl
        ));
    }
}
