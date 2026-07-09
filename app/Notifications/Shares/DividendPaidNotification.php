<?php

namespace App\Notifications\Shares;

use App\Models\DividendPayout;
use App\Settings\SmsSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Number;

class DividendPaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public DividendPayout $payout) {}

    public function via(object $notifiable): array
    {
        $channels = ['database', 'mail'];

        if (app(SmsSettings::class)->sms_dividend_paid) {
            $channels[] = 'kudisms';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Dividend Payment Received')
            ->markdown('mail.shares.dividend-paid', [
                'notifiable' => $notifiable,
                'payout' => $this->payout,
            ]);
    }

    public function toSms(object $notifiable): string
    {
        return "Hi {$notifiable->name}, a dividend of ".Number::currency($this->payout->amount).' has been credited to your wallet.';
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
