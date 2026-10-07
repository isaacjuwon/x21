<?php

declare(strict_types=1);

use App\Enums\Plans\ServiceType;
use App\Http\Entities\PurchaseAirtime;
use App\Http\Entities\PurchaseCable;
use App\Http\Entities\PurchaseData;
use App\Http\Entities\ServiceResponse;
use App\Integrations\Contracts\Providers\VtuProvider;
use App\Integrations\Failover\FailoverVtuProvider;
use App\Models\Brand;
use App\Models\Plan;

beforeEach(function () {
    config()->set('api.providers.failover.providers', ['vtugate', 'vtpass']);
});

test('failover provider dynamically swaps apiCode for unified Plan during data failover', function () {
    $brand = Brand::factory()->create(['api_code' => 'mtn']);

    $plan = Plan::create([
        'brand_id' => $brand->id,
        'service_type' => ServiceType::Data,
        'name' => 'MTN 1GB',
        'type' => 'SME',
        'api_code' => 'DEFAULT_FALLBACK',
        'price' => 300.00,
        'cost_price' => 250.00,
        'status' => true,
    ]);

    $plan->syncProviderCodes([
        'vtugate' => 'VTU_MTN_1GB_CODE',
        'vtpass' => 'VTPASS_MTN_1GB_CODE',
    ]);

    $entity = new PurchaseData(
        network: 'mtn',
        mobileNumber: '08012345678',
        apiCode: 'INITIAL_UNRESOLVED_CODE',
        reference: 'REF-DATA-SWAP-1',
        planId: $plan->id,
        planType: Plan::class,
    );

    $primary = Mockery::mock(VtuProvider::class);
    $secondary = Mockery::mock(VtuProvider::class);

    // Primary receives entity with swapped vtugate code
    $primary->shouldReceive('purchaseData')
        ->once()
        ->withArgs(function (PurchaseData $arg) {
            return $arg->apiCode === 'VTU_MTN_1GB_CODE'
                && $arg->mobileNumber === '08012345678'
                && $arg->reference === 'REF-DATA-SWAP-1';
        })
        ->andReturn(new ServiceResponse(code: 102, description: ['message' => 'VTUGate provider maintenance']));

    // Secondary receives entity with swapped vtpass code
    $secondary->shouldReceive('purchaseData')
        ->once()
        ->withArgs(function (PurchaseData $arg) {
            return $arg->apiCode === 'VTPASS_MTN_1GB_CODE'
                && $arg->mobileNumber === '08012345678'
                && $arg->reference === 'REF-DATA-SWAP-1';
        })
        ->andReturn(new ServiceResponse(code: 101, description: ['ref' => 'VTPASS-SUCCESS-REF', 'status' => 'delivered']));

    $failover = new FailoverVtuProvider(
        providers: ['vtugate' => $primary, 'vtpass' => $secondary],
        retryAfter: 60,
        failoverOnUnsuccessful: true,
    );

    $response = $failover->purchaseData($entity);

    expect($response->isSuccessful())->toBeTrue()
        ->and($response->description['ref'])->toBe('VTPASS-SUCCESS-REF')
        ->and($failover->isProviderDead('vtugate'))->toBeTrue();
});

test('failover provider dynamically swaps network for unified Plan during airtime failover', function () {
    $brand = Brand::factory()->create(['api_code' => 'airtel']);

    $plan = Plan::create([
        'brand_id' => $brand->id,
        'service_type' => ServiceType::Airtime,
        'name' => 'Airtel Airtime',
        'api_code' => 'AIRTEL_AIRTIME',
        'price' => 500.00,
        'cost_price' => 485.00,
        'status' => true,
    ]);

    $plan->syncProviderCodes([
        'vtugate' => 'airtel_vtugate_net_id',
        'vtpass' => 'airtel_vtpass_net_id',
    ]);

    $entity = new PurchaseAirtime(
        network: 'airtel',
        amount: 500,
        mobileNumber: '08022222222',
        reference: 'REF-AIRTIME-SWAP-1',
        planId: $plan->id,
        planType: Plan::class,
    );

    $primary = Mockery::mock(VtuProvider::class);
    $secondary = Mockery::mock(VtuProvider::class);

    $primary->shouldReceive('purchaseAirtime')
        ->once()
        ->withArgs(fn (PurchaseAirtime $arg) => $arg->network === 'airtel_vtugate_net_id')
        ->andReturn(new ServiceResponse(code: 102, description: ['message' => 'VTUGate gateway busy']));

    $secondary->shouldReceive('purchaseAirtime')
        ->once()
        ->withArgs(fn (PurchaseAirtime $arg) => $arg->network === 'airtel_vtpass_net_id')
        ->andReturn(new ServiceResponse(code: 101, description: ['ref' => 'VTPASS-AIRTIME-OK']));

    $failover = new FailoverVtuProvider(
        providers: ['vtugate' => $primary, 'vtpass' => $secondary],
        retryAfter: 60,
        failoverOnUnsuccessful: true,
    );

    $response = $failover->purchaseAirtime($entity);

    expect($response->isSuccessful())->toBeTrue()
        ->and($response->description['ref'])->toBe('VTPASS-AIRTIME-OK');
});

test('failover provider dynamically swaps apiCode for cable bouquets and electricity during failover', function () {
    $brand = Brand::factory()->create(['api_code' => 'dstv']);

    $cablePlan = Plan::create([
        'brand_id' => $brand->id,
        'service_type' => ServiceType::Cable,
        'name' => 'DStv Compact',
        'api_code' => 'DEFAULT_DSTV',
        'price' => 12500.00,
        'cost_price' => 12000.00,
        'status' => true,
    ]);

    $cablePlan->syncProviderCodes([
        'vtugate' => 'DSTV_COMPACT_VTG',
        'vtpass' => 'dstv-compact-vtpass',
    ]);

    $entity = new PurchaseCable(
        service: 'dstv',
        smartcardNumber: '1020304050',
        apiCode: 'INITIAL_CODE',
        amount: 12500,
        reference: 'REF-CABLE-1',
        planId: $cablePlan->id,
        planType: Plan::class,
    );

    $primary = Mockery::mock(VtuProvider::class);
    $secondary = Mockery::mock(VtuProvider::class);

    $primary->shouldReceive('purchaseCable')
        ->once()
        ->withArgs(fn (PurchaseCable $arg) => $arg->apiCode === 'DSTV_COMPACT_VTG')
        ->andReturn(new ServiceResponse(code: 102, description: ['message' => 'Unavailable']));

    $secondary->shouldReceive('purchaseCable')
        ->once()
        ->withArgs(fn (PurchaseCable $arg) => $arg->apiCode === 'dstv-compact-vtpass')
        ->andReturn(new ServiceResponse(code: 101, description: ['ref' => 'CABLE-SUCCESS']));

    $failover = new FailoverVtuProvider(
        providers: ['vtugate' => $primary, 'vtpass' => $secondary],
        retryAfter: 60,
        failoverOnUnsuccessful: true,
    );

    $response = $failover->purchaseCable($entity);

    expect($response->isSuccessful())->toBeTrue();
});
