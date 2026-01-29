<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Share\BuyShareAction;
use App\Actions\Share\SellShareAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Share\BuyShareRequest;
use App\Http\Requests\Api\Share\SellShareRequest;
use App\Http\Resources\ShareResource;
use App\Models\Share;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShareController extends Controller
{
    /**
     * List user's shares.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $shares = Share::where('holder_type', User::class)
            ->where('holder_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return ShareResource::collection($shares);
    }

    /**
     * Buy new shares.
     */
    public function buy(BuyShareRequest $request, BuyShareAction $action): ShareResource
    {
        $share = $action->handle($request->user(), $request->payload());

        return new ShareResource($share);
    }

    /**
     * Sell shares.
     */
    public function sell(SellShareRequest $request, SellShareAction $action): \Illuminate\Http\JsonResponse
    {
        $action->handle($request->user(), $request->payload());

        return response()->json(['message' => 'Shares sold successfully.']);
    }
}
