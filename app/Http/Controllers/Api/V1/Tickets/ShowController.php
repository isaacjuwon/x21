<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tickets;

use App\Http\Resources\Api\V1\Tickets\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Tickets', 'Support ticket management')]
#[Authenticated]
final class ShowController
{
    #[ResponseFromApiResource(TicketResource::class, Ticket::class)]
    public function __invoke(Request $request, Ticket $ticket): TicketResource
    {
        if ($ticket->user_id !== $request->user()->id) {
            abort(404);
        }

        return new TicketResource($ticket->load('replies.user'));
    }
}
