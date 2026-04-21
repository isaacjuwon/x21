<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tickets;

use App\Http\Resources\Api\V1\Tickets\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Tickets', 'Support ticket management')]
#[Authenticated]
final class IndexController
{
    #[ResponseFromApiResource(TicketResource::class, Ticket::class, collection: true, paginate: 15)]
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $tickets = $request->user()
            ->tickets()
            ->withCount('replies')
            ->latest()
            ->paginate(15);

        return TicketResource::collection($tickets);
    }
}
