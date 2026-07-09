<?php

namespace App\Notifications\Wallets;

use App\Models\Transaction;
use App\Settings\SmsSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Number;

class TransactionReversedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Transaction $original,
        public Transaction $refund,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database', 'mail'];

        if (app(SmsSettings::class)->sms_transaction_reversed) {
            $channels[] = 'kudisms';
        }

        return $channels;
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

    public function toSms(object $notifiable): string
    {
        return "Hi {$notifiable->name}, a refund of ".Number::currency($this->original->amount)." has been processed to your wallet (Ref: {$this->refund->reference}).";
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
