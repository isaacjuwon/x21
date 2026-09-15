<?php

use Inudev\BannerPopup\Models\BannerPopup;

it('returns only active banners within date window', function () {
    BannerPopup::create([
        'name' => 'Test',
        'is_active' => true,
        'trigger' => 'page_load',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
    ]);

    expect(BannerPopup::active()->count())->toBe(1);
});

it('excludes expired banners', function () {
    BannerPopup::create([
        'name' => 'Expired',
        'is_active' => true,
        'trigger' => 'page_load',
        'ends_at' => now()->subDay(),
    ]);

    expect(BannerPopup::active()->count())->toBe(0);
});
