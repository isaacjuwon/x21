<?php

namespace Inudev\BannerPopup;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BannerPopupServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/banner-popup.php',
            'banner-popup'
        );

        $this->app->singleton('banner-popup', fn () => new BannerPopupManager);
    }

    public function boot(): void
    {
        $this->registerPublishables();
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'banner-popup');
        $this->loadRoutesFrom(__DIR__.'/../src/Http/routes.php');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'banner-popup');

        $this->registerBladeComponents();
    }

    protected function registerPublishables(): void
    {
        // Config
        $this->publishes([
            __DIR__.'/../config/banner-popup.php' => config_path('banner-popup.php'),
        ], 'banner-popup-config');

        // Migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'banner-popup-migrations');

        // Frontend assets (JS + CSS)
        $this->publishes([
            __DIR__.'/../resources/js' => public_path('vendor/banner-popup/js'),
            __DIR__.'/../resources/css' => public_path('vendor/banner-popup/css'),
        ], 'banner-popup-assets');

        // Blade views (for customization)
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/banner-popup'),
        ], 'banner-popup-views');

        // Translations
        $this->publishes([
            __DIR__.'/../resources/lang' => lang_path('vendor/banner-popup'),
        ], 'banner-popup-translations');
    }

    protected function registerBladeComponents(): void
    {
        $this->callAfterResolving('blade.compiler', function () {
            Blade::component(
                'banner-popup::components.banner-popup',
                'banner-popup'
            );
        });
    }
}
