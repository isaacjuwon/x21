<?php

namespace App\Http\Controllers\Api\V1\Shares;

use App\Actions\Shares\RejectShareOrderAction;
use App\Http\Requests\Api\V1\Shares\StoreShareOrderRejectionRequest;
use App\Http\Resources\Api\V1\Shares\ShareOrderResource;
use App\Models\ShareOrder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Shares', 'Share orders and holdings')]
#[Authenticated]
class ShareOrderRejectionController
{
    use AuthorizesRequests;

    #[BodyParam('rejection_reason', 'string', description: 'Reason for rejecting the share order', required: true, example: 'Insufficient documentation')]

    #[ResponseFromApiResource(ShareOrderResource::class, ShareOrder::class)]
    #[Response(['message' => 'Order is not in a pending state.'], status: 422)]
    #[Response(['message' => 'This action is unauthorized.'], status: 403)]
    public function __invoke(
        StoreShareOrderRejectionRequest $request,
        ShareOrder $order,
        RejectShareOrderAction $action,
    ): ShareOrderResource {
        $this->authorize('reject', $order);

        return new ShareOrderResource(
            $action->handle($order, $request->user(), $request->rejection_reason)
        );
    }
}
