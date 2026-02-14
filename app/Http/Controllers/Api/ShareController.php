<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Share\BuyShareAction;
use App\Actions\Share\SellShareAction;
use App\Http\Requests\Api\Share\BuyShareRequest;
use App\Http\Requests\Api\Share\SellShareRequest;
use App\Http\Resources\ShareResource;
use App\Models\Share;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShareController extends ApiController
{
    /**
     * List user's shares.
     */
    public function index(Request $request): JsonResponse
    {
        $shares = Share::where('holder_type', User::class)
            ->where('holder_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        $shares->setCollection($shares->getCollection()->mapInto(ShareResource::class));

        return $this->paginatedResponse($shares, 'Shares retrieved successfully');
    }

    /**
     * Buy new shares.
     */
    public function buy(BuyShareRequest $request, BuyShareAction $action): JsonResponse
    {
        $share = $action->handle($request->user(), $request->payload());

        return $this->createdResponse(new ShareResource($share), 'Shares purchased successfully');
    }

    /**
     * Sell shares.
     */
    public function sell(SellShareRequest $request, SellShareAction $action): JsonResponse
    {
        $action->handle($request->user(), $request->payload());

        return $this->successResponse(null, 'Shares sold successfully.');
    }
}
