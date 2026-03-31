<?php

namespace App\Http\Controllers\Api\V1\Shares;

use App\Http\Resources\Api\V1\Shares\SharePriceHistoryResource;
use App\Models\SharePriceHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Shares', 'Share orders and holdings')]
#[Authenticated]
class SharePriceHistoryController
{
    #[ResponseFromApiResource(SharePriceHistoryResource::class, SharePriceHistory::class, collection: true, paginate: 15)]
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        return SharePriceHistoryResource::collection(
            SharePriceHistory::latest('created_at')->paginate(15)
        );
    }
}
