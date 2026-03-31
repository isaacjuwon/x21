<?php

namespace App\Actions\Tickets;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Events\Tickets\TicketCreated;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\Tickets\TicketCreatedNotification;

class OpenTicketAction
{
    public function handle(User $user, string $subject, string $message, TicketPriority $priority = TicketPriority::Medium): Ticket
    {
        $ticket = Ticket::create([
            'user_id' => $user->id,
            'subject' => $subject,
            'message' => $message,
            'status' => TicketStatus::Open,
            'priority' => $priority,
        ]);

        TicketCreated::dispatch($ticket);

        $user->notify(new TicketCreatedNotification($ticket));

        return $ticket;
    }
}
