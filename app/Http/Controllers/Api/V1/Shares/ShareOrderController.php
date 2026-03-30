<?php

namespace App\Http\Controllers\Api\V1\Shares;

use App\Actions\Shares\PlaceBuyOrderAction;
use App\Actions\Shares\PlaceSellOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Shares\StoreBuyOrderRequest;
use App\Http\Requests\Api\V1\Shares\StoreSellOrderRequest;
use App\Http\Resources\Api\V1\Shares\ShareOrderResource;
use App\Models\ShareOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShareOrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return ShareOrderResource::collection(
            auth()->user()->shareOrders()->latest()->paginate(15)
        );
    }

    public function show(Request $request, ShareOrder $order): ShareOrderResource
    {
        if ($order->user_id !== auth()->id()) {
            abort(404);
        }

        return new ShareOrderResource($order);
    }

    public function buy(StoreBuyOrderRequest $request, PlaceBuyOrderAction $action): JsonResponse
    {
        $order = $action->handle(auth()->user(), $request->quantity);

        return (new ShareOrderResource($order))->response()->setStatusCode(201);
    }

    public function sell(StoreSellOrderRequest $request, PlaceSellOrderAction $action): JsonResponse
    {
        $order = $action->handle(auth()->user(), $request->quantity);

        return (new ShareOrderResource($order))->response()->setStatusCode(201);
    }
}
