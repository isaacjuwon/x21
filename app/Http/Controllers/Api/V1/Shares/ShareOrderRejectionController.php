<?php

namespace App\Http\Controllers\Api\V1\Shares;

use App\Actions\Shares\RejectShareOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Shares\StoreShareOrderRejectionRequest;
use App\Http\Resources\Api\V1\Shares\ShareOrderResource;
use App\Models\ShareOrder;

class ShareOrderRejectionController extends Controller
{
    public function store(
        StoreShareOrderRejectionRequest $request,
        ShareOrder $order,
        RejectShareOrderAction $action,
    ): ShareOrderResource {
        $this->authorize('reject', $order);

        $order = $action->handle($order, auth()->user(), $request->rejection_reason);

        return new ShareOrderResource($order);
    }
}
