<?php

namespace App\Actions\Tickets;

use App\Events\Tickets\TicketCreated;
use App\Models\Ticket;
use App\Models\User;

class CreateTicket
{
    public function execute(User $user, string $subject, string $message, string $priority = 'medium'): Ticket
    {
        $ticket = $user->tickets()->create([
            'subject' => $subject,
            'message' => $message,
            'status' => 'open',
            'priority' => $priority,
        ]);

        event(new TicketCreated($ticket));

        return $ticket;
    }
}
