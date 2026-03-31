<?php

namespace App\Actions\Tickets;

use App\Enums\Tickets\TicketStatus;
use App\Events\Tickets\TicketReplied;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use App\Notifications\Tickets\TicketRepliedNotification;

class ReplyToTicketAction
{
    public function handle(Ticket $ticket, User $replier, string $message): TicketReply
    {
        $isStaff = $replier->hasRole(['super_admin', 'admin']);

        $reply = $ticket->replies()->create([
            'user_id' => $replier->id,
            'message' => $message,
            'is_staff' => $isStaff,
        ]);

        // If staff replies, move to in_progress; if user replies on resolved, reopen
        if ($isStaff && $ticket->status === TicketStatus::Open) {
            $ticket->update(['status' => TicketStatus::InProgress]);
        } elseif (! $isStaff && $ticket->status === TicketStatus::Resolved) {
            $ticket->update(['status' => TicketStatus::Open]);
        }

        TicketReplied::dispatch($reply);

        // Notify the other party
        if ($isStaff) {
            $ticket->user->notify(new TicketRepliedNotification($reply));
        } else {
            // Notify assigned staff if any
            if ($ticket->assignedTo) {
                $ticket->assignedTo->notify(new TicketRepliedNotification($reply));
            }
        }

        return $reply;
    }
}
