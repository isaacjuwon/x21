<?php

namespace Inudev\BannerPopup\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inudev\BannerPopup\BannerPopupServiceProvider;
use Inudev\BannerPopup\Facades\BannerPopup;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            MediaLibraryServiceProvider::class,
            BannerPopupServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'BannerPopup' => BannerPopup::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // SQLite in-memory for speed
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Fake disk so Spatie Media doesn't write to real storage
        $app['config']->set('filesystems.disks.testing', [
            'driver' => 'local',
            'root' => storage_path('framework/testing/disks/media'),
        ]);
        $app['config']->set('media-library.disk_name', 'testing');

        // Package config
        $app['config']->set('banner-popup.table_name', 'banner_popups');
        $app['config']->set('banner-popup.cookie_prefix', 'bp_');
        $app['config']->set('banner-popup.route_prefix', 'banner-popup');
        $app['config']->set('banner-popup.route_middleware', ['web']);
    }

    protected function defineDatabaseMigrations(): void
    {
        // Package migration
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Spatie Media Library migration (ships with the package)
        $this->loadMigrationsFrom(
            base_path('vendor/spatie/laravel-medialibrary/database/migrations')
        );
    }
}
