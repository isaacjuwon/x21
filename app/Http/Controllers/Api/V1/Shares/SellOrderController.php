<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Shares;

use App\Actions\Shares\PlaceSellOrderAction;
use App\Http\Requests\Api\V1\Shares\StoreSellOrderRequest;
use App\Http\Resources\Api\V1\Shares\ShareOrderResource;
use App\Models\ShareOrder;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Shares', 'Share orders and holdings')]
#[Authenticated]
final class SellOrderController
{
    public function __construct(
        private readonly PlaceSellOrderAction $action,
    ) {}

    #[BodyParam('quantity', 'integer', description: 'Number of shares to sell (min: 1)', required: true, example: 5)]
    #[ResponseFromApiResource(ShareOrderResource::class, ShareOrder::class, status: 201)]
    #[Response(['message' => 'Insufficient shares.'], status: 422)]
    #[Response(['message' => 'Holding period not met.', 'earliest_sell_date' => '2025-01-01'], status: 422)]
    public function __invoke(StoreSellOrderRequest $request): JsonResponse
    {
        $order = $this->action->handle(
            user: $request->user(),
            quantity: $request->quantity,
        );

        return (new ShareOrderResource($order))->response()->setStatusCode(201);
    }
}
