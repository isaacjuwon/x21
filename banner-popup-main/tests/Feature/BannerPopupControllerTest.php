<?php

// ─────────────────────────────────────────────────────────────────────────────
// GET /banner-popup/active
// ─────────────────────────────────────────────────────────────────────────────

it('returns 200 with an empty array when no banners exist', function () {
    $this->getJson('/banner-popup/active')
        ->assertOk()
        ->assertJson([]);
});

it('returns active banners as JSON', function () {
    banner(['name' => 'Promo']);

    $this->getJson('/banner-popup/active')
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['trigger' => 'page_load']);
});

it('returns the expected payload keys for each banner', function () {
    banner();

    $this->getJson('/banner-popup/active')
        ->assertOk()
        ->assertJsonStructure([
            '*' => ['id', 'trigger', 'trigger_delay', 'link_url', 'link_target', 'show_frequency', 'images'],
        ]);
});

it('returns images with desktop, tablet and mobile keys', function () {
    banner();

    $this->getJson('/banner-popup/active')
        ->assertOk()
        ->assertJsonStructure([
            '*' => [
                'images' => ['desktop', 'tablet', 'mobile'],
            ],
        ]);
});

it('excludes inactive banners', function () {
    banner(['is_active' => false]);

    $this->getJson('/banner-popup/active')
        ->assertOk()
        ->assertJsonCount(0);
});

it('excludes expired banners', function () {
    banner(['ends_at' => now()->subDay()]);

    $this->getJson('/banner-popup/active')
        ->assertOk()
        ->assertJsonCount(0);
});

it('excludes banners that have not started yet', function () {
    banner(['starts_at' => now()->addDay()]);

    $this->getJson('/banner-popup/active')
        ->assertOk()
        ->assertJsonCount(0);
});

// ─────────────────────────────────────────────────────────────────────────────
// GET /banner-popup/active?page=route.name  — target_pages filtering
// ─────────────────────────────────────────────────────────────────────────────

it('returns an unrestricted banner regardless of page param', function () {
    banner(['target_pages' => null]);

    $this->getJson('/banner-popup/active?page=home')->assertJsonCount(1);
    $this->getJson('/banner-popup/active?page=about')->assertJsonCount(1);
    $this->getJson('/banner-popup/active')->assertJsonCount(1);
});

it('returns a targeted banner only for its route', function () {
    banner(['target_pages' => ['home']]);

    $this->getJson('/banner-popup/active?page=home')->assertJsonCount(1);
    $this->getJson('/banner-popup/active?page=about')->assertJsonCount(0);
});

it('hides a targeted banner when no page param is sent', function () {
    banner(['target_pages' => ['home']]);

    $this->getJson('/banner-popup/active')->assertJsonCount(0);
});

it('returns multiple banners when several match the route', function () {
    banner(['name' => 'Global',   'target_pages' => null]);
    banner(['name' => 'Homepage', 'target_pages' => ['home']]);

    $this->getJson('/banner-popup/active?page=home')->assertJsonCount(2);
    $this->getJson('/banner-popup/active?page=about')->assertJsonCount(1);
});

// ─────────────────────────────────────────────────────────────────────────────
// POST /banner-popup/{id}/seen
// ─────────────────────────────────────────────────────────────────────────────

it('returns 204 when marking a banner as seen', function () {
    $banner = banner();

    $this->postJson("/banner-popup/{$banner->id}/seen")
        ->assertNoContent();
});

it('returns 404 for a non-existent banner id', function () {
    $this->postJson('/banner-popup/9999/seen')
        ->assertNotFound();
});

it('returns 404 for a soft-deleted banner', function () {
    $banner = banner();
    $banner->delete();

    $this->postJson("/banner-popup/{$banner->id}/seen")
        ->assertNotFound();
});
