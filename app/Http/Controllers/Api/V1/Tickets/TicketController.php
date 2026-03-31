<?php

namespace App\Http\Controllers\Api\V1\Tickets;

use App\Actions\Tickets\OpenTicketAction;
use App\Enums\Tickets\TicketPriority;
use App\Http\Requests\Api\V1\Tickets\StoreTicketRequest;
use App\Http\Resources\Api\V1\Tickets\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Tickets', 'Support ticket management')]
#[Authenticated]
class TicketController
{
    #[ResponseFromApiResource(TicketResource::class, Ticket::class, collection: true, paginate: 15)]
    public function index(Request $request): AnonymousResourceCollection
    {
        $tickets = $request->user()
            ->tickets()
            ->withCount('replies')
            ->latest()
            ->paginate(15);

        return TicketResource::collection($tickets);
    }

    #[ResponseFromApiResource(TicketResource::class, Ticket::class)]
    public function show(Request $request, Ticket $ticket): TicketResource
    {
        if ($ticket->user_id !== $request->user()->id) {
            abort(404);
        }

        return new TicketResource($ticket->load('replies.user'));
    }

    #[ResponseFromApiResource(TicketResource::class, Ticket::class)]
    public function store(StoreTicketRequest $request, OpenTicketAction $action): JsonResponse
    {
        $ticket = $action->handle(
            $request->user(),
            $request->validated('subject'),
            $request->validated('message'),
            TicketPriority::tryFrom($request->validated('priority') ?? '') ?? TicketPriority::Medium,
        );

        return (new TicketResource($ticket))
            ->response()
            ->setStatusCode(201);
    }
}
