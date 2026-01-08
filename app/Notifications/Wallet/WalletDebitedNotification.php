<?php

namespace App\Notifications\Wallet;

use App\Enums\WalletType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WalletDebitedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public float $amount,
        public WalletType $type,
        public ?string $notes = null
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Wallet Debited')
            ->markdown('mail.wallet.debited', [
                'amount' => $this->amount,
                'type' => $this->type,
                'notes' => $this->notes,
                'user' => $notifiable
            ]);
    }
}
