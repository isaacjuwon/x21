<?php

use Inudev\BannerPopup\BannerPopupManager;

beforeEach(function () {
    $this->manager = new BannerPopupManager;
});

// ─────────────────────────────────────────────────────────────────────────────
// active() — no page filter
// ─────────────────────────────────────────────────────────────────────────────

it('returns active banners with no target restriction when no page is given', function () {
    banner(['target_pages' => null]);

    expect($this->manager->active())->toHaveCount(1);
});

it('returns empty collection when no banners exist', function () {
    expect($this->manager->active())->toBeEmpty();
});

it('excludes inactive banners', function () {
    banner(['is_active' => false]);

    expect($this->manager->active())->toBeEmpty();
});

// ─────────────────────────────────────────────────────────────────────────────
// active() — target_pages filtering
// ─────────────────────────────────────────────────────────────────────────────

it('shows a banner with no target_pages on any route', function () {
    banner(['target_pages' => null]);

    expect($this->manager->active('home'))->toHaveCount(1);
    expect($this->manager->active('about'))->toHaveCount(1);
    expect($this->manager->active(null))->toHaveCount(1);
});

it('shows a targeted banner only on its designated route', function () {
    banner(['target_pages' => ['about']]);

    expect($this->manager->active('about'))->toHaveCount(1);
    expect($this->manager->active('home'))->toBeEmpty();
});

it('shows a banner targeted at multiple routes on each of them', function () {
    banner(['target_pages' => ['home', 'about', 'contact']]);

    expect($this->manager->active('home'))->toHaveCount(1);
    expect($this->manager->active('about'))->toHaveCount(1);
    expect($this->manager->active('contact'))->toHaveCount(1);
    expect($this->manager->active('pricing'))->toBeEmpty();
});

it('excludes a targeted banner when no route name is provided', function () {
    banner(['target_pages' => ['home']]);

    // No route name → targeted banners should not appear
    expect($this->manager->active(null))->toBeEmpty();
});

it('mixes unrestricted and targeted banners correctly', function () {
    banner(['name' => 'Global',   'target_pages' => null]);
    banner(['name' => 'Homepage', 'target_pages' => ['home']]);
    banner(['name' => 'About',    'target_pages' => ['about']]);

    expect($this->manager->active('home'))->toHaveCount(2);   // Global + Homepage
    expect($this->manager->active('about'))->toHaveCount(2);  // Global + About
    expect($this->manager->active('pricing'))->toHaveCount(1); // Global only
});

// ─────────────────────────────────────────────────────────────────────────────
// active() — date window respected by manager
// ─────────────────────────────────────────────────────────────────────────────

it('excludes expired banners even if they match the target page', function () {
    banner(['target_pages' => ['home'], 'ends_at' => now()->subDay()]);

    expect($this->manager->active('home'))->toBeEmpty();
});

it('excludes future banners even if they match the target page', function () {
    banner(['target_pages' => ['home'], 'starts_at' => now()->addDay()]);

    expect($this->manager->active('home'))->toBeEmpty();
});

// ─────────────────────────────────────────────────────────────────────────────
// find()
// ─────────────────────────────────────────────────────────────────────────────

it('finds an existing banner by id', function () {
    $banner = banner(['name' => 'Findable']);

    $found = $this->manager->find($banner->id);

    expect($found)->not->toBeNull()
        ->and($found->name)->toBe('Findable');
});

it('finds a soft-deleted banner', function () {
    $banner = banner();
    $banner->delete();

    expect($this->manager->find($banner->id))->not->toBeNull();
});

it('returns null for a non-existent id', function () {
    expect($this->manager->find(9999))->toBeNull();
});
