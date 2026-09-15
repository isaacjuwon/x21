<?php

use Inudev\BannerPopup\Models\BannerPopup;

// ─────────────────────────────────────────────────────────────────────────────
// scopeActive — visibility flag
// ─────────────────────────────────────────────────────────────────────────────

it('includes banners with is_active = true', function () {
    banner(['is_active' => true]);

    expect(BannerPopup::active()->count())->toBe(1);
});

it('excludes banners with is_active = false', function () {
    banner(['is_active' => false]);

    expect(BannerPopup::active()->count())->toBe(0);
});

// ─────────────────────────────────────────────────────────────────────────────
// scopeActive — date window
// ─────────────────────────────────────────────────────────────────────────────

it('includes banners with no date restriction', function () {
    banner(['starts_at' => null, 'ends_at' => null]);

    expect(BannerPopup::active()->count())->toBe(1);
});

it('includes banners whose window is currently open', function () {
    banner(['starts_at' => now()->subHour(), 'ends_at' => now()->addHour()]);

    expect(BannerPopup::active()->count())->toBe(1);
});

it('excludes banners that have not started yet', function () {
    banner(['starts_at' => now()->addDay()]);

    expect(BannerPopup::active()->count())->toBe(0);
});

it('excludes banners that have already ended', function () {
    banner(['ends_at' => now()->subDay()]);

    expect(BannerPopup::active()->count())->toBe(0);
});

it('includes banners with only starts_at set and already started', function () {
    banner(['starts_at' => now()->subMinute(), 'ends_at' => null]);

    expect(BannerPopup::active()->count())->toBe(1);
});

it('includes banners with only ends_at set and not yet ended', function () {
    banner(['starts_at' => null, 'ends_at' => now()->addMinute()]);

    expect(BannerPopup::active()->count())->toBe(1);
});

// ─────────────────────────────────────────────────────────────────────────────
// isCurrentlyActive()
// ─────────────────────────────────────────────────────────────────────────────

it('isCurrentlyActive returns true for an active banner with open window', function () {
    $banner = banner(['is_active' => true, 'starts_at' => now()->subDay(), 'ends_at' => now()->addDay()]);

    expect($banner->isCurrentlyActive())->toBeTrue();
});

it('isCurrentlyActive returns false when is_active is false', function () {
    $banner = banner(['is_active' => false]);

    expect($banner->isCurrentlyActive())->toBeFalse();
});

it('isCurrentlyActive returns false when start is in the future', function () {
    $banner = banner(['is_active' => true, 'starts_at' => now()->addHour()]);

    expect($banner->isCurrentlyActive())->toBeFalse();
});

it('isCurrentlyActive returns false when end has passed', function () {
    $banner = banner(['is_active' => true, 'ends_at' => now()->subHour()]);

    expect($banner->isCurrentlyActive())->toBeFalse();
});

// ─────────────────────────────────────────────────────────────────────────────
// imageUrl() — breakpoint fallback chain
// ─────────────────────────────────────────────────────────────────────────────

it('imageUrl returns null when no media is attached', function () {
    $banner = banner();

    expect($banner->imageUrl('desktop'))->toBeNull();
    expect($banner->imageUrl('tablet'))->toBeNull();
    expect($banner->imageUrl('mobile'))->toBeNull();
});

// ─────────────────────────────────────────────────────────────────────────────
// toFrontendArray()
// ─────────────────────────────────────────────────────────────────────────────

it('toFrontendArray returns the expected keys', function () {
    $banner = banner(['trigger' => 'delay', 'trigger_delay' => 5, 'show_frequency' => 7]);

    $payload = $banner->toFrontendArray();

    expect($payload)
        ->toHaveKeys(['id', 'trigger', 'trigger_delay', 'link_url', 'link_target', 'show_frequency', 'images'])
        ->and($payload['trigger'])->toBe('delay')
        ->and($payload['trigger_delay'])->toBe(5)
        ->and($payload['show_frequency'])->toBe(7)
        ->and($payload['images'])->toHaveKeys(['desktop', 'tablet', 'mobile']);
});

// ─────────────────────────────────────────────────────────────────────────────
// Casts
// ─────────────────────────────────────────────────────────────────────────────

it('casts target_pages as array', function () {
    $banner = banner(['target_pages' => ['home', 'about']]);

    expect($banner->fresh()->target_pages)->toBeArray()->toContain('home', 'about');
});

it('casts is_active as boolean', function () {
    $banner = banner(['is_active' => true]);

    expect($banner->fresh()->is_active)->toBeBool()->toBeTrue();
});

it('casts show_frequency as integer', function () {
    $banner = banner(['show_frequency' => 7]);

    expect($banner->fresh()->show_frequency)->toBeInt()->toBe(7);
});

// ─────────────────────────────────────────────────────────────────────────────
// SoftDeletes
// ─────────────────────────────────────────────────────────────────────────────

it('soft-deletes a banner and excludes it from default queries', function () {
    $banner = banner();
    $banner->delete();

    expect(BannerPopup::count())->toBe(0)
        ->and(BannerPopup::withTrashed()->count())->toBe(1);
});

it('excludes soft-deleted banners from the active scope', function () {
    $banner = banner(['is_active' => true]);
    $banner->delete();

    expect(BannerPopup::active()->count())->toBe(0);
});
