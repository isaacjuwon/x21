<?php

namespace App\Http\Controllers\Api\V1\Shares;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Shares\ShareHoldingResource;
use App\Models\ShareHolding;
use Illuminate\Http\Request;

class ShareHoldingController extends Controller
{
    public function show(Request $request): ShareHoldingResource
    {
        $holding = auth()->user()->shareHolding;

        if (! $holding) {
            return new ShareHoldingResource(new ShareHolding(['quantity' => 0, 'acquired_at' => null]));
        }

        return new ShareHoldingResource($holding);
    }
}
