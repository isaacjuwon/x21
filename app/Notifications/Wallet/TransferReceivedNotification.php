<?php

namespace App\Notifications\Wallet;

use App\Enums\WalletType;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransferReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $sender,
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
            ->subject('Transfer Received')
            ->markdown('mail.wallet.transfer-received', [
                'amount' => $this->amount,
                'type' => $this->type,
                'notes' => $this->notes,
                'recipient' => $notifiable,
                'sender' => $this->sender
            ]);
    }
}
