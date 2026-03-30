<?php

namespace App\Notifications\Shares;

use App\Models\DividendPayout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DividendPaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public DividendPayout $payout) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'payout_id' => $this->payout->id,
            'dividend_id' => $this->payout->dividend_id,
            'amount' => $this->payout->amount,
        ];
    }
}
