<?php

namespace App\Notifications\Wallets;

use App\Models\Transaction;
use App\Settings\SmsSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Number;

class WalletWithdrawnNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Transaction $transaction) {}

    public function via(object $notifiable): array
    {
        $channels = ['database', 'mail'];

        if (app(SmsSettings::class)->sms_wallet_withdrawn) {
            $channels[] = 'kudisms';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Withdrawal Initiated')
            ->markdown('mail.wallets.withdrawal-initiated', [
                'notifiable' => $notifiable,
                'transaction' => $this->transaction,
            ]);
    }

    public function toSms(object $notifiable): string
    {
        $amount = Number::currency($this->transaction->meta['amount'] ?? $this->transaction->amount);
        $accountName = $this->transaction->meta['account_name'] ?? 'your account';
        $bankName = $this->transaction->meta['bank_name'] ?? '';

        return "Hi {$notifiable->name}, your withdrawal of {$amount} to {$accountName} ({$bankName}) has been initiated. Ref: {$this->transaction->reference}.";
    }

    public function toArray(object $notifiable): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->meta['amount'] ?? $this->transaction->amount,
            'reference' => $this->transaction->reference,
            'account_name' => $this->transaction->meta['account_name'] ?? null,
            'bank_name' => $this->transaction->meta['bank_name'] ?? null,
        ];
    }
}
