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
     * Get share portfolio statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $settings = app(\App\Settings\ShareSettings::class);
        $holdingPeriod = $settings->holding_period;
        
        $shares = $user->shares()->where('quantity', '>', 0)->get();
        
        $totalShares = $shares->sum('quantity');
        $approvedShares = $shares->where('status', \App\Enums\ShareStatus::APPROVED)->sum('quantity');
        
        $eligibilityDate = now()->subDays($holdingPeriod);
        $matureShares = $shares
            ->where('status', \App\Enums\ShareStatus::APPROVED)
            ->filter(fn ($share) => $share->approved_at?->lte($eligibilityDate))
            ->sum('quantity');

        return $this->successResponse([
            'total_shares' => $totalShares,
            'approved_shares' => $approvedShares,
            'mature_shares' => $matureShares,
            'total_value' => $totalShares * $settings->share_price,
            'mature_value' => $matureShares * $settings->share_price,
            'price_per_share' => $settings->share_price,
        ], 'Share portfolio statistics retrieved successfully');
    }

    /**
     * Get share settings and policy.
     */
    public function settings(): JsonResponse
    {
        $settings = app(\App\Settings\ShareSettings::class);

        return $this->successResponse([
            'share_price' => $settings->share_price,
            'holding_period_days' => $settings->holding_period,
            'interest_rate' => $settings->share_interest_rate,
            'require_admin_approval' => $settings->require_admin_approval,
        ], 'Share settings retrieved successfully');
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
