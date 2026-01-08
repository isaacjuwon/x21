<?php

namespace App\Notifications\Shares;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SharesSoldNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $quantity,
        public float $price,
        public float $totalAmount
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Shares Sold')
            ->markdown('mail.shares.sold', [
                'quantity' => $this->quantity,
                'price' => $this->price,
                'totalAmount' => $this->totalAmount,
                'user' => $notifiable
            ]);
    }
}
