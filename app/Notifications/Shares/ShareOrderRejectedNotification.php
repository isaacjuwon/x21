<?php

namespace App\Notifications\Shares;

use App\Models\ShareOrder;
use App\Settings\SmsSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Number;

class ShareOrderRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ShareOrder $order) {}

    public function via(object $notifiable): array
    {
        $channels = ['database', 'mail'];

        if (app(SmsSettings::class)->sms_share_order_rejected) {
            $channels[] = 'kudisms';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Share Order Has Been Rejected')
            ->markdown('mail.shares.order-rejected', [
                'notifiable' => $notifiable,
                'order' => $this->order,
            ]);
    }

    public function toSms(object $notifiable): string
    {
        return "Hi {$notifiable->name}, your {$this->order->type->getLabel()} order for {$this->order->quantity} shares (".Number::currency($this->order->total_amount).") was rejected. Reason: {$this->order->rejection_reason}.";
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'type' => $this->order->type,
            'quantity' => $this->order->quantity,
            'total_amount' => $this->order->total_amount,
            'status' => $this->order->status,
            'rejection_reason' => $this->order->rejection_reason,
        ];
    }
}
