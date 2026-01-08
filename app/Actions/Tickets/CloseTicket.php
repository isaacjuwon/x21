<?php

namespace App\Actions\Tickets;

use App\Models\Ticket;

class CloseTicket
{
    public function execute(Ticket $ticket): void
    {
        $ticket->update(['status' => 'closed']);
    }
}
