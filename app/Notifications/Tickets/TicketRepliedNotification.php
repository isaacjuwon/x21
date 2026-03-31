<?php

namespace App\Notifications\Tickets;

use App\Models\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketRepliedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TicketReply $reply) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Reply on Ticket #{$this->reply->ticket_id}")
            ->markdown('mail.tickets.replied', [
                'notifiable' => $notifiable,
                'reply' => $this->reply,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->reply->ticket_id,
            'reply_id' => $this->reply->id,
            'is_staff' => $this->reply->is_staff,
        ];
    }
}
