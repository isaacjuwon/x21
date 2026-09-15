<?php

namespace App\Facades;

use App\Managers\BannerPopupManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection<int, \App\Models\BannerPopup> active(?string $routeName = null)
 * @method static \App\Models\BannerPopup|null find(int $id)
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
