<?php

namespace App\Notifications\Shares;

use App\Models\ShareOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShareOrderRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ShareOrder $order) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
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
