<?php

namespace App\Http\Controllers\Api\V1\Shares;

use App\Http\Resources\Api\V1\Shares\DividendPayoutResource;
use App\Models\DividendPayout;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Shares', 'Share orders and holdings')]
#[Authenticated]
class DividendPayoutController
{
    #[ResponseFromApiResource(DividendPayoutResource::class, DividendPayout::class, collection: true, paginate: 15)]
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        return DividendPayoutResource::collection(
            $request->user()->dividendPayouts()->latest()->paginate(15)
        );
    }
}
