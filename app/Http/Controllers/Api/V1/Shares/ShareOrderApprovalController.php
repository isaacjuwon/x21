<?php

namespace App\Http\Controllers\Api\V1\Shares;

use App\Actions\Shares\ApproveBuyOrderAction;
use App\Actions\Shares\ApproveSellOrderAction;
use App\Enums\Shares\ShareOrderType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Shares\ShareOrderResource;
use App\Models\ShareOrder;
use Illuminate\Http\Request;

class ShareOrderApprovalController extends Controller
{
    public function store(
        Request $request,
        ShareOrder $order,
        ApproveBuyOrderAction $buyAction,
        ApproveSellOrderAction $sellAction,
    ): ShareOrderResource {
        $this->authorize('approve', $order);

        if ($order->type === ShareOrderType::Buy) {
            $order = $buyAction->handle($order, auth()->user());
        } else {
            $order = $sellAction->handle($order, auth()->user());
        }

        return new ShareOrderResource($order);
    }
}
