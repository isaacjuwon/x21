<?php

namespace Inudev\BannerPopup\Facades;

use Illuminate\Support\Facades\Facade;
use Inudev\BannerPopup\BannerPopupManager;

/**
 * @method static \Illuminate\Support\Collection active(?string $routeName = null)
 * @method static \Inudev\BannerPopup\Models\BannerPopup|null find(int $id)
 *
 * @see BannerPopupManager
 */
class BannerPopup extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'banner-popup';
    }
}
