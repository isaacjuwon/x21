<?php

namespace Inudev\BannerPopup\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Inudev\BannerPopup\Facades\BannerPopup;
use Inudev\BannerPopup\Models\BannerPopup as BannerPopupModel;

class BannerPopupController extends Controller
{
    /**
     * GET /banner-popup/active?page=route.name
     *
     * Returns the JSON payload consumed by banner-popup.js.
     * Filtering by date window + is_active happens in the scope.
     * Filtering by target_pages happens in BannerPopupManager.
     */
    public function active(Request $request): JsonResponse
    {
        $routeName = $request->query('page');

        $banners = BannerPopup::active($routeName)
            ->map(fn (BannerPopupModel $b) => $b->toFrontendArray());

        return response()->json($banners);
    }

    /**
     * POST /banner-popup/{banner}/seen
     *
     * Lightweight impression endpoint. Currently returns 204.
     * Extend this to write to an impressions table if you need analytics.
     */
    public function seen(BannerPopupModel $banner): Response
    {
        // Hook for future impression logging:
        // event(new BannerImpression($banner));

        return response()->noContent();
    }
}
