<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Shares;

use App\Http\Resources\Api\V1\Shares\ShareOrderResource;
use App\Models\ShareOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Shares', 'Share orders and holdings')]
#[Authenticated]
final class IndexOrdersController
{
    #[ResponseFromApiResource(ShareOrderResource::class, ShareOrder::class, collection: true, paginate: 15)]
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        return ShareOrderResource::collection(
            $request->user()->shareOrders()->latest()->paginate(15)
        );
    }
}
