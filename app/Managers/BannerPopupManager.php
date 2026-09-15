<?php

namespace App\Managers;

use App\Models\BannerPopup;
use Illuminate\Support\Collection;

class BannerPopupManager
{
    /**
     * Return all currently active banners, optionally filtered by route name.
     *
     * @return Collection<int, BannerPopup>
     */
    public function active(?string $routeName = null): Collection
    {
        return BannerPopup::active()
            ->get()
            ->filter(function (BannerPopup $banner) use ($routeName): bool {
                if (empty($banner->target_pages)) {
                    return true;
                }

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
