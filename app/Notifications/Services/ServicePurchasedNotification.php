<?php

namespace App\Notifications\Services;

use App\Models\Plan;
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

    public ?Plan $plan;

    public function __construct(
        public TopupTransaction $transaction,
        ?Plan $plan = null,
    ) {
        $this->plan = $plan ?? $transaction->plan;
    }

    public function via(object $notifiable): array
    {
        $channels = ['database', 'mail'];

        try {
            if (app(SmsSettings::class)->sms_service_purchased) {
                $channels[] = 'kudisms';
            }
        } catch (\Throwable) {
            // Settings unavailable or not initialized
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
                'plan' => $this->plan ?? $this->transaction->plan,
            ]);
    }

    public function toSms(object $notifiable): string
    {
        $type = $this->transaction->type->getLabel();
        $amount = Number::currency($this->transaction->amount);
        $recipient = $this->transaction->recipient;
        $planDetails = $this->plan?->name ? " ({$this->plan->name})" : '';

        return "Hi {$notifiable->name}, your {$type}{$planDetails} purchase of {$amount} for {$recipient} was successful. Ref: {$this->transaction->reference}.";
    }

    public function toArray(object $notifiable): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'type' => $this->transaction->type,
            'amount' => $this->transaction->amount,
            'recipient' => $this->transaction->recipient,
            'reference' => $this->transaction->reference,
            'plan_id' => $this->plan?->id,
            'plan_name' => $this->plan?->name,
        ];
    }
}
