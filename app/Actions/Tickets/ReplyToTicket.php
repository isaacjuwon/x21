<?php

namespace App\Actions\Tickets;

use App\Events\Tickets\TicketReplied;
use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketReply;

class ReplyToTicket
{
    public function execute(Ticket $ticket, User $user, string $message): TicketReply
    {
        $reply = $ticket->replies()->create([
            'user_id' => $user->id,
            'message' => $message,
        ]);

        if ($user->id !== $ticket->user_id) {
             // If admin replies, we might want to update status to 'answered' or something, 
             // but for now let's just keep 'open' or 'in_progress' if we had that.
             // We'll keep it simple.
        }

        event(new TicketReplied($reply));

        return $reply;
    }
}
