<?php

use App\Models\Brand;
use App\Models\DataPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function seedChainConfig(array $providers): void
{
    config()->set('api.providers.failover.providers', $providers);
}

function makePlanWithCodes(array $providerCodeMap, array $legacyColumns = []): DataPlan
{
    $brand = Brand::factory()->create(['api_code' => 'mtn']);

    $plan = DataPlan::factory()->for($brand)->create($legacyColumns);

    foreach ($providerCodeMap as $provider => $code) {
        $plan->providerCodes()->create([
            'provider' => $provider,
            'code' => $code,
        ]);
    }

    return $plan->load('providerCodes');
}

beforeEach(function () {
    seedChainConfig(['vtugate', 'vtpass']);
});

test('apiCodeFor returns exact provider code when relationship row exists', function () {
    $plan = makePlanWithCodes([
        'vtugate' => 'VTG_MTN_1GB',
        'vtpass' => 'mtn-data-1000',
    ]);

    expect($plan->apiCodeFor('vtugate'))->toBe('VTG_MTN_1GB')
        ->and($plan->apiCodeFor('vtpass'))->toBe('mtn-data-1000')
        ->and($plan->apiCodeFor('some-unknown-provider'))->toBeNull();
});

test('resolveApiCode walks configured failover chain in declared order and returns first hit', function () {
    $plan = makePlanWithCodes([
        'vtpass' => 'mtn-data-1000',
    ]);

    expect($plan->resolveApiCode('vtugate'))->toBe('mtn-data-1000');
});

test('resolveApiCode returns preferred provider first even when later providers exist', function () {
    $plan = makePlanWithCodes([
        'vtugate' => 'VTG_MTN_2GB',
        'vtpass' => 'mtn-data-2000',
    ]);

    expect($plan->resolveApiCode('vtugate'))->toBe('VTG_MTN_2GB');
});

test('resolveApiCode returns null when no relationship rows exist — no accidental legacy api_code fallback', function () {
    $plan = makePlanWithCodes([], [
        'api_code' => 'OLD_LEGACY_CODE_DEAD_DATA',
        'vtpass_code' => 'OLD_VTPASS_LEGACY_COL',
    ]);

    expect($plan->resolveApiCode('vtugate'))->toBeNull()
        ->and($plan->resolveApiCode('vtpass'))->toBeNull()
        ->and($plan->apiCodeFor('vtugate'))->toBeNull();
});

test('getApiCodeAttribute aliases resolveApiCode against head of configured chain', function () {
    $planOnlyVtpass = makePlanWithCodes([
        'vtpass' => 'fallback-mtn-500',
    ]);

    expect($planOnlyVtpass->api_code)->toBe('fallback-mtn-500')
        ->and($planOnlyVtpass->api_code)->toBe($planOnlyVtpass->resolveApiCode('vtugate'));

    $planBoth = makePlanWithCodes([
        'vtugate' => 'PRIMARY-500',
        'vtpass' => 'fallback-mtn-500',
    ]);

    expect($planBoth->api_code)->toBe('PRIMARY-500')
        ->and($planBoth->api_code)->toBe($planBoth->resolveApiCode('vtugate'));
});

test('providerCodes relation registers correctly as morphMany planable', function () {
    $plan = makePlanWithCodes([
        'vtugate' => 'X',
        'vtpass' => 'Y',
    ]);

    expect($plan->providerCodes)->toHaveCount(2);
    foreach ($plan->providerCodes as $row) {
        expect($row->planable_type)->toBe(DataPlan::class)
            ->and($row->planable_id)->toBe($plan->id);
    }
});
