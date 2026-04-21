<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Shares;

use App\Http\Resources\Api\V1\Shares\ShareOrderResource;
use App\Models\ShareOrder;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Shares', 'Share orders and holdings')]
#[Authenticated]
final class ShowOrderController
{
    #[ResponseFromApiResource(ShareOrderResource::class, ShareOrder::class)]
    #[Response(['message' => 'Not Found'], status: 404)]
    public function __invoke(Request $request, ShareOrder $order): ShareOrderResource
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        return new ShareOrderResource($order);
    }
}
