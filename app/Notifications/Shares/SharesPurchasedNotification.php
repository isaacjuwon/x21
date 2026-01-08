<?php

namespace App\Notifications\Shares;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SharesPurchasedNotification extends Notification implements ShouldQueue
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
            ->subject('Shares Purchased')
            ->markdown('mail.shares.purchased', [
                'quantity' => $this->quantity,
                'price' => $this->price,
                'totalAmount' => $this->totalAmount,
                'user' => $notifiable
            ]);
    }
}
