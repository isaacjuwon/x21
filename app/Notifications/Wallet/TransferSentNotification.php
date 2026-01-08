<?php

namespace App\Notifications\Wallet;

use App\Enums\WalletType;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransferSentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $recipient,
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
            ->subject('Transfer Sent Successfully')
            ->markdown('mail.wallet.transfer-sent', [
                'amount' => $this->amount,
                'type' => $this->type,
                'notes' => $this->notes,
                'sender' => $notifiable,
                'recipient' => $this->recipient
            ]);
    }
}
