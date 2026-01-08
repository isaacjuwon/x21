<?php

namespace App\Notifications\Services;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServicePurchasedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $serviceType,
        public string $productName,
        public float $amount,
        public string $transactionReference,
        public ?string $transactionUrl = null,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Service Purchase Confirmation - ' . ucfirst($this->serviceType))
            ->markdown('mail.services.purchased', [
                'serviceType' => $this->serviceType,
                'productName' => $this->productName,
                'amount' => $this->amount,
                'transactionReference' => $this->transactionReference,
                'transactionUrl' => $this->transactionUrl,
                'user' => $notifiable
            ]);
    }
}
