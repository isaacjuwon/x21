<?php

namespace App\Http\Controllers\Api\V1\Shares;

use App\Http\Resources\Api\V1\Shares\ShareHoldingResource;
use App\Models\ShareHolding;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Shares', 'Share orders and holdings')]
#[Authenticated]
class ShareHoldingController
{
    #[ResponseFromApiResource(ShareHoldingResource::class, ShareHolding::class)]
    public function __invoke(Request $request): ShareHoldingResource
    {
        $holding = $request->user()->shareHolding
            ?? new ShareHolding(['quantity' => 0, 'acquired_at' => null]);

        return new ShareHoldingResource($holding);
    }
}
