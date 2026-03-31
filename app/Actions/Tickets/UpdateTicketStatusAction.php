<?php

namespace App\Actions\Tickets;

use App\Enums\Tickets\TicketStatus;
use App\Events\Tickets\TicketStatusChanged;
use App\Models\Ticket;
use App\Notifications\Tickets\TicketStatusChangedNotification;

class UpdateTicketStatusAction
{
    public function handle(Ticket $ticket, TicketStatus $status): Ticket
    {
        $previous = $ticket->status;

        $ticket->update([
            'status' => $status,
            'resolved_at' => $status === TicketStatus::Resolved ? now() : null,
        ]);

        TicketStatusChanged::dispatch($ticket, $previous);

        $ticket->user->notify(new TicketStatusChangedNotification($ticket));

        return $ticket->fresh();
    }
}
