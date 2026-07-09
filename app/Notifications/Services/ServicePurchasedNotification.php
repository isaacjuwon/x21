<?php

namespace App\Notifications\Services;

use App\Models\TopupTransaction;
use App\Settings\SmsSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Number;

class ServicePurchasedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TopupTransaction $transaction) {}

    public function via(object $notifiable): array
    {
        $channels = ['database', 'mail'];

        if (app(SmsSettings::class)->sms_service_purchased) {
            $channels[] = 'kudisms';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->transaction->type->getLabel()} Purchase Successful")
            ->markdown('mail.services.service-purchased', [
                'notifiable' => $notifiable,
                'transaction' => $this->transaction,
            ]);
    }

    public function toSms(object $notifiable): string
    {
        $type = $this->transaction->type->getLabel();
        $amount = Number::currency($this->transaction->amount);
        $recipient = $this->transaction->recipient;

        return "Hi {$notifiable->name}, your {$type} purchase of {$amount} for {$recipient} was successful. Ref: {$this->transaction->reference}.";
    }

    public function toArray(object $notifiable): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'type' => $this->transaction->type,
            'amount' => $this->transaction->amount,
            'recipient' => $this->transaction->recipient,
            'reference' => $this->transaction->reference,
        ];
    }
}
