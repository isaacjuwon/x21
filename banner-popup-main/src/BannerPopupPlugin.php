<?php

namespace Inudev\BannerPopup;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Inudev\BannerPopup\Filament\Resources\BannerPopupResource;

class BannerPopupPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'banner-popup';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            BannerPopupResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
