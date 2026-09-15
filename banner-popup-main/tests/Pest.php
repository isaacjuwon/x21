<?php

use Inudev\BannerPopup\Models\BannerPopup;
use Inudev\BannerPopup\Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
| All tests use our custom TestCase by default. It boots Orchestra
| Testbench with the package's ServiceProvider and an in-memory SQLite DB.
|
*/
uses(TestCase::class)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Shared helper: make a BannerPopup with sensible defaults
|--------------------------------------------------------------------------
| Usage inside any test:
|
|   $banner = banner(is_active: true, trigger: 'delay', trigger_delay: 5);
|
*/
function banner(array $overrides = []): BannerPopup
{
    return BannerPopup::create(array_merge([
        'name' => 'Test Banner',
        'is_active' => true,
        'trigger' => 'page_load',
        'trigger_delay' => null,
        'target_pages' => null,
        'link_url' => null,
        'link_target' => '_self',
        'starts_at' => null,
        'ends_at' => null,
        'show_frequency' => 0,
    ], $overrides));
}
