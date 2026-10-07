<?php

declare(strict_types=1);

use App\Integrations\Failover\FailoverVtuProvider;
use App\Managers\ApiManager;
use App\Models\Brand;
use App\Models\Plan;
use App\Settings\IntegrationSettings;

test('api manager reads failover provider order from spatie integration settings', function () {
    $settings = app(IntegrationSettings::class);
    $settings->vtu_failover_providers = ['vtpass', 'vtugate'];
    $settings->save();

    $manager = app(ApiManager::class);
    /** @var FailoverVtuProvider $provider */
    $provider = $manager->vtuProvider('failover');

    $providers = $provider->getProviders();

    // Verify order matches Spatie settings: vtpass first, vtugate second
    expect(array_keys($providers))->toBe(['vtpass', 'vtugate']);
});

test('has provider codes resolves chain according to spatie settings order', function () {
    $settings = app(IntegrationSettings::class);
    $settings->vtu_failover_providers = ['vtpass', 'vtugate'];
    $settings->save();

    $brand = Brand::factory()->create();
    $plan = Plan::factory()->create(['brand_id' => $brand->id]);

    $plan->syncProviderCodes([
        'vtugate' => 'VTUGATE_CODE',
        'vtpass' => 'VTPASS_CODE',
    ]);

    // Head of chain is vtpass based on spatie setting
    expect($plan->resolveApiCode())->toBe('VTPASS_CODE');

    // If we reorder Spatie settings:
    $settings->vtu_failover_providers = ['vtugate', 'vtpass'];
    $settings->save();

    // Now head of chain is vtugate
    expect($plan->resolveApiCode())->toBe('VTUGATE_CODE');
});
