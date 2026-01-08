<?php

namespace App\Notifications\Tickets;

use App\Models\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReplyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TicketReply $reply)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isTicketOwner = $notifiable->id === $this->reply->ticket->user_id;
        $url = $isTicketOwner
            ? route('tickets.show', $this->reply->ticket_id)
            : route('admin.tickets.show', $this->reply->ticket_id);

        return (new MailMessage)
            ->subject('New Reply on Ticket #' . $this->reply->ticket_id)
            ->markdown('mail.tickets.reply', [
                'ticket' => $this->reply->ticket,
                'message' => $this->reply->message,
                'url' => $url,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->reply->ticket_id,
            'reply_id' => $this->reply->id,
            'message' => $this->reply->message,
        ];
    }
}
