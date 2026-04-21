<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tickets;

use App\Actions\Tickets\OpenTicketAction;
use App\Enums\Tickets\TicketPriority;
use App\Http\Requests\Api\V1\Tickets\StoreTicketRequest;
use App\Http\Resources\Api\V1\Tickets\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Tickets', 'Support ticket management')]
#[Authenticated]
final class StoreController
{
    public function __construct(
        private readonly OpenTicketAction $action,
    ) {}

    #[ResponseFromApiResource(TicketResource::class, Ticket::class)]
    public function __invoke(StoreTicketRequest $request): JsonResponse
    {
        $ticket = $this->action->handle(
            user: $request->user(),
            subject: $request->validated('subject'),
            message: $request->validated('message'),
            priority: TicketPriority::tryFrom($request->validated('priority') ?? '') ?? TicketPriority::Medium,
        );

        return (new TicketResource($ticket))
            ->response()
            ->setStatusCode(201);
    }
}
