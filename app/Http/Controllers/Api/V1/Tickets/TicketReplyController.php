<?php

namespace App\Http\Controllers\Api\V1\Tickets;

use App\Actions\Tickets\ReplyToTicketAction;
use App\Http\Requests\Api\V1\Tickets\StoreTicketReplyRequest;
use App\Http\Resources\Api\V1\Tickets\TicketReplyResource;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;

#[Group('Tickets', 'Support ticket management')]
#[Authenticated]
class TicketReplyController
{
    public function store(StoreTicketReplyRequest $request, Ticket $ticket, ReplyToTicketAction $action): JsonResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            abort(404);
        }

        if (! $ticket->status->isOpen()) {
            abort(422, 'This ticket is closed and no longer accepting replies.');
        }

        $reply = $action->handle($ticket, $request->user(), $request->validated('message'));

        return (new TicketReplyResource($reply->load('user')))
            ->response()
            ->setStatusCode(201);
    }
}
