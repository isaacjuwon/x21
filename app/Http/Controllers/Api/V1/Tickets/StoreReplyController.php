<?php

declare(strict_types=1);

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
final class StoreReplyController
{
    public function __construct(
        private readonly ReplyToTicketAction $action,
    ) {}

    public function __invoke(StoreTicketReplyRequest $request, Ticket $ticket): JsonResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            abort(404);
        }

        if (! $ticket->status->isOpen()) {
            abort(422, 'This ticket is closed and no longer accepting replies.');
        }

        $reply = $this->action->handle(
            ticket: $ticket,
            user: $request->user(),
            message: $request->validated('message'),
        );

        return (new TicketReplyResource($reply->load('user')))
            ->response()
            ->setStatusCode(201);
    }
}
