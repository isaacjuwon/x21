<?php

declare(strict_types=1);

use App\Enums\Plans\ServiceType;
use App\Http\Entities\PurchaseCable;
use App\Http\Entities\PurchaseData;
use App\Http\Entities\ServiceResponse;
use App\Integrations\Vtugate\VtugateProvider;
use App\Managers\ApiManager;
use App\Models\Brand;
use App\Models\Plan;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('vtugate provider adapts data request using unified Plan model before making request', function () {
    $brand = Brand::factory()->create([
        'name' => 'MTN Nigeria',
        'slug' => 'mtn',
        'api_code' => 'mtn',
    ]);

    $plan = Plan::create([
        'brand_id' => $brand->id,
        'service_type' => ServiceType::Data,
        'name' => 'MTN 2GB Monthly',
        'type' => 'SME',
        'api_code' => 'INTERNAL_CODE',
        'price' => 500.00,
        'cost_price' => 460.00,
        'status' => true,
    ]);

    $plan->setProviderCode('vtugate', 'vtugate-mtn-2000mb');

    Http::fake([
        '*/api/v1/data/buy' => function (Request $request) {
            $data = $request->data();

            expect($data['network'])->toBe('mtn-data')
                ->and($data['plan'])->toBe('vtugate-mtn-2000mb')
                ->and($data['phone'])->toBe('08012345678')
                ->and($data['ref'])->toBe('REF-VTUGATE-ADAPT');

            return Http::response([
                'status' => true,
                'code' => 101,
                'message' => 'Transaction Successful',
                'ref' => 'REF-VTUGATE-ADAPT',
            ], 200);
        },
    ]);

    /** @var VtugateProvider $provider */
    $provider = app(ApiManager::class)->vtuProvider('vtugate');

    $entity = new PurchaseData(
        network: 'unresolved-network',
        mobileNumber: '08012345678',
        apiCode: 'unresolved-code',
        reference: 'REF-VTUGATE-ADAPT',
        planId: $plan->id,
        planType: Plan::class,
    );

    $response = $provider->purchaseData($entity);

    expect($response)->toBeInstanceOf(ServiceResponse::class)
        ->and($response->isSuccessful())->toBeTrue()
        ->and($response->description['ref'])->toContain('REF-VTUGATE-ADAPT');
});

test('vtugate provider adapts cable request using unified Plan model before making request', function () {
    $brand = Brand::factory()->create([
        'name' => 'DSTV Nigeria',
        'slug' => 'dstv',
        'api_code' => 'dstv',
    ]);

    $plan = Plan::create([
        'brand_id' => $brand->id,
        'service_type' => ServiceType::Cable,
        'name' => 'DSTV Compact',
        'type' => 'monthly',
        'api_code' => 'INTERNAL_CODE',
        'price' => 12500.00,
        'cost_price' => 12000.00,
        'status' => true,
    ]);

    $plan->setProviderCode('vtugate', 'dstv-compact-code');

    Http::fake([
        '*/api/v1/cable/buy' => function (Request $request) {
            $data = $request->data();

            expect($data['service_id'])->toBe('dstv')
                ->and($data['bouquet'])->toBe('dstv-compact-code')
                ->and($data['smartcard_number'])->toBe('1234567890')
                ->and($data['amount'])->toBe(12500)
                ->and($data['ref'])->toBe('REF-CABLE-1');

            return Http::response([
                'status' => true,
                'code' => 101,
                'message' => 'Cable purchase successful',
                'ref' => 'REF-CABLE-1',
            ], 200);
        },
    ]);

    /** @var VtugateProvider $provider */
    $provider = app(ApiManager::class)->vtuProvider('vtugate');

    $entity = new PurchaseCable(
        service: 'unresolved-service',
        smartcardNumber: '1234567890',
        apiCode: 'unresolved-bouquet',
        amount: 1000,
        reference: 'REF-CABLE-1',
        planId: $plan->id,
        planType: Plan::class,
    );

    $response = $provider->purchaseCable($entity);

    expect($response->isSuccessful())->toBeTrue();
});

test('vtugate provider normalizes timeout and connection errors to pending status', function () {
    Http::fake([
        '*/api/v1/data/buy' => Http::response(null, 504),
    ]);

    /** @var VtugateProvider $provider */
    $provider = app(ApiManager::class)->vtuProvider('vtugate');

    $entity = new PurchaseData(
        network: 'mtn',
        mobileNumber: '08012345678',
        apiCode: 'mtn-data-1000',
        reference: 'REF-VTUGATE-TIMEOUT',
    );

    $response = $provider->purchaseData($entity);

    // Must be mapped to pending (code 100) instead of failing or throwing unhandled exception
    expect($response->code)->toBe(100)
        ->and($response->isSuccessful())->toBeFalse()
        ->and($response->description['status'])->toBe('pending');
});
