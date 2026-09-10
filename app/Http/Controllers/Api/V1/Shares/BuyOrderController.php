<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Shares;

use App\Actions\Shares\PlaceBuyOrderAction;
use App\Http\Requests\Api\V1\Shares\StoreBuyOrderRequest;
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
final class BuyOrderController
{
    public function __construct(
        private readonly PlaceBuyOrderAction $action,
    ) {}

    #[BodyParam('quantity', 'integer', description: 'Number of shares to buy (min: 1)', required: true, example: 10)]
    #[ResponseFromApiResource(ShareOrderResource::class, ShareOrder::class, status: 201)]
    #[Response(['message' => 'Insufficient available shares.'], status: 422)]
    #[Response(['message' => 'Insufficient funds.'], status: 422)]
    public function __invoke(StoreBuyOrderRequest $request): JsonResponse
    {
        $order = $this->action->handle(
            user: $request->user(),
            quantity: $request->quantity,
        );

        return (new ShareOrderResource($order))->response()->setStatusCode(201);
    }
}
