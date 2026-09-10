<?php

namespace App\Http\Controllers\Api\V1\Shares;

use App\Actions\Shares\ApproveBuyOrderAction;
use App\Actions\Shares\ApproveSellOrderAction;
use App\Enums\Shares\ShareOrderType;
use App\Http\Resources\Api\V1\Shares\ShareOrderResource;
use App\Models\ShareOrder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Shares', 'Share orders and holdings')]
#[Authenticated]
class ShareOrderApprovalController
{
    use AuthorizesRequests;

    #[ResponseFromApiResource(ShareOrderResource::class, ShareOrder::class)]
    #[Response(['message' => 'Order is not in a pending state.'], status: 422)]
    #[Response(['message' => 'This action is unauthorized.'], status: 403)]
    public function __invoke(
        Request $request,
        ShareOrder $order,
        ApproveBuyOrderAction $buyAction,
        ApproveSellOrderAction $sellAction,
    ): ShareOrderResource {
        $this->authorize('approve', $order);

        $order = $order->type === ShareOrderType::Buy
            ? $buyAction->handle($order, $request->user())
            : $sellAction->handle($order, $request->user());

        return new ShareOrderResource($order);
    }
}
