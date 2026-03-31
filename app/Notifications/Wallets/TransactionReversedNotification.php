<?php

namespace App\Notifications\Wallets;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransactionReversedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Transaction $original,
        public Transaction $refund,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Wallet Refund Processed')
            ->markdown('mail.wallets.transaction-reversed', [
                'notifiable' => $notifiable,
                'original' => $this->original,
                'refund' => $this->refund,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Wallet Refunded',
            'body' => 'Your wallet has been refunded '.number_format($this->original->amount, 2).' due to a failed transaction.',
            'original_reference' => $this->original->reference,
            'refund_reference' => $this->refund->reference,
            'amount' => $this->original->amount,
            'reason' => $this->original->failure_reason,
        ];
    }
}
