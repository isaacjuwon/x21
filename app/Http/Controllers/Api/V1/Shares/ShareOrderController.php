<?php

namespace App\Http\Controllers\Api\V1\Shares;

use App\Actions\Shares\PlaceBuyOrderAction;
use App\Actions\Shares\PlaceSellOrderAction;
use App\Http\Requests\Api\V1\Shares\StoreBuyOrderRequest;
use App\Http\Requests\Api\V1\Shares\StoreSellOrderRequest;
use App\Http\Resources\Api\V1\Shares\ShareOrderResource;
use App\Models\ShareOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Shares', 'Share orders and holdings')]
#[Authenticated]
class ShareOrderController
{
    #[ResponseFromApiResource(ShareOrderResource::class, ShareOrder::class, collection: true, paginate: 15)]
    public function index(Request $request): AnonymousResourceCollection
    {
        return ShareOrderResource::collection(
            $request->user()->shareOrders()->latest()->paginate(15)
        );
    }

    #[ResponseFromApiResource(ShareOrderResource::class, ShareOrder::class)]
    #[Response(['message' => 'Not Found'], status: 404)]
    public function show(Request $request, ShareOrder $order): ShareOrderResource
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        return new ShareOrderResource($order);
    }

    #[BodyParam('quantity', 'integer', description: 'Number of shares to buy (min: 1)', required: true, example: 10)]
    #[ResponseFromApiResource(ShareOrderResource::class, ShareOrder::class, status: 201)]
    #[Response(['message' => 'Insufficient available shares.'], status: 422)]
    #[Response(['message' => 'Insufficient funds.'], status: 422)]
    public function buy(StoreBuyOrderRequest $request, PlaceBuyOrderAction $action): JsonResponse
    {
        $order = $action->handle($request->user(), $request->quantity);

        return (new ShareOrderResource($order))->response()->setStatusCode(201);
    }

    #[BodyParam('quantity', 'integer', description: 'Number of shares to sell (min: 1)', required: true, example: 5)]
    #[ResponseFromApiResource(ShareOrderResource::class, ShareOrder::class, status: 201)]
    #[Response(['message' => 'Insufficient shares.'], status: 422)]
    #[Response(['message' => 'Holding period not met.', 'earliest_sell_date' => '2025-01-01'], status: 422)]
    public function sell(StoreSellOrderRequest $request, PlaceSellOrderAction $action): JsonResponse
    {
        $order = $action->handle($request->user(), $request->quantity);

        return (new ShareOrderResource($order))->response()->setStatusCode(201);
    }
}
