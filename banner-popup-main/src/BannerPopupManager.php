<?php

namespace Inudev\BannerPopup;

use Illuminate\Support\Collection;
use Inudev\BannerPopup\Models\BannerPopup;

class BannerPopupManager
{
    /**
     * Return all currently active banners, optionally filtered by route name.
     */
    public function active(?string $routeName = null): Collection
    {
        return BannerPopup::active()
            ->get()
            ->filter(function (BannerPopup $banner) use ($routeName) {
                // No target restriction → show everywhere
                if (empty($banner->target_pages)) {
                    return true;
                }

                // Route name must be in the target list
                return $routeName && in_array($routeName, $banner->target_pages, true);
            })
            ->values();
    }

    /**
     * Find a single banner by ID (including trashed, for admin use).
     */
    public function find(int $id): ?BannerPopup
    {
        return BannerPopup::withTrashed()->find($id);
    }
}
