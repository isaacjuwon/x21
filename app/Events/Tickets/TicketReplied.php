<?php

namespace App\Events\Tickets;

use App\Models\TicketReply;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketReplied
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public TicketReply $reply) {}
}
