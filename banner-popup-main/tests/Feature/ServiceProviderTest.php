<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inudev\BannerPopup\BannerPopupManager;
use Inudev\BannerPopup\Facades\BannerPopup;

// ─────────────────────────────────────────────────────────────────────────────
// ServiceProvider — container bindings
// ─────────────────────────────────────────────────────────────────────────────

it('binds the BannerPopupManager into the container', function () {
    expect(app('banner-popup'))->toBeInstanceOf(BannerPopupManager::class);
});

it('resolves through the Facade', function () {
    expect(BannerPopup::active())->toBeInstanceOf(Collection::class);
});

// ─────────────────────────────────────────────────────────────────────────────
// ServiceProvider — config
// ─────────────────────────────────────────────────────────────────────────────

it('merges the package config', function () {
    expect(config('banner-popup'))->toBeArray()
        ->and(config('banner-popup.table_name'))->toBe('banner_popups')
        ->and(config('banner-popup.cookie_prefix'))->toBe('bp_')
        ->and(config('banner-popup.route_prefix'))->toBe('banner-popup');
});

// ─────────────────────────────────────────────────────────────────────────────
// ServiceProvider — routes
// ─────────────────────────────────────────────────────────────────────────────

it('registers the active route', function () {
    expect(Route::has('banner-popup.active'))->toBeTrue();
});

it('registers the seen route', function () {
    expect(Route::has('banner-popup.seen'))->toBeTrue();
});

// ─────────────────────────────────────────────────────────────────────────────
// ServiceProvider — migrations
// ─────────────────────────────────────────────────────────────────────────────

it('creates the banner_popups table', function () {
    expect(Schema::hasTable('banner_popups'))->toBeTrue();
});

it('banner_popups table has all expected columns', function () {
    $columns = Schema::getColumnListing('banner_popups');

    foreach (['id', 'name', 'is_active', 'trigger', 'trigger_delay',
        'target_pages', 'link_url', 'link_target',
        'starts_at', 'ends_at', 'show_frequency',
        'deleted_at', 'created_at', 'updated_at'] as $column) {
        expect($columns)->toContain($column);
    }
});
