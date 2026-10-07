<?php

declare(strict_types=1);

use App\Enums\Plans\ServiceType;
use App\Http\Entities\PurchaseData;
use App\Http\Entities\ServiceResponse;
use App\Integrations\Vtpass\VtpassProvider;
use App\Managers\ApiManager;
use App\Models\Brand;
use App\Models\Plan;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('vtpass provider adapts request using unified Plan model before making request', function () {
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

    $plan->setProviderCode('vtpass', 'mtn-data-2000mb');

    Http::fake([
        'https://vtpass.com/api/pay' => function (Request $request) {
            $data = $request->data();

            expect($data['serviceID'])->toBe('mtn-data')
                ->and($data['variation_code'])->toBe('mtn-data-2000mb')
                ->and($data['billersCode'])->toBe('08012345678')
                ->and($data['phone'])->toBe('08012345678')
                ->and($data['request_id'])->toBe('REF-VTPASS-ADAPT');

            return Http::response([
                'code' => '000',
                'response_description' => 'TRANSACTION SUCCESSFUL',
                'requestId' => 'REF-VTPASS-ADAPT',
                'content' => ['transactions' => ['status' => 'delivered']],
            ], 200);
        },
    ]);

    /** @var VtpassProvider $provider */
    $provider = app(ApiManager::class)->vtuProvider('vtpass');

    // Entity is passed with neutral codes; VtpassProvider adapts it using $plan
    $entity = new PurchaseData(
        network: 'unresolved-network',
        mobileNumber: '08012345678',
        apiCode: 'unresolved-code',
        reference: 'REF-VTPASS-ADAPT',
        planId: $plan->id,
        planType: Plan::class,
    );

    $response = $provider->purchaseData($entity);

    expect($response)->toBeInstanceOf(ServiceResponse::class)
        ->and($response->isSuccessful())->toBeTrue()
        ->and($response->description['ref'])->toBe('REF-VTPASS-ADAPT');
});

test('vtpass provider normalizes timeout and connection errors to pending status', function () {
    Http::fake([
        'https://vtpass.com/api/pay' => Http::response(null, 504),
    ]);

    /** @var VtpassProvider $provider */
    $provider = app(ApiManager::class)->vtuProvider('vtpass');

    $entity = new PurchaseData(
        network: 'mtn',
        mobileNumber: '08012345678',
        apiCode: 'mtn-data-1000',
        reference: 'REF-TIMEOUT-1',
    );

    $response = $provider->purchaseData($entity);

    // Must be mapped to pending (code 100) instead of failing or throwing unhandled exception
    expect($response->code)->toBe(100)
        ->and($response->isSuccessful())->toBeFalse()
        ->and($response->description['status'])->toBe('pending');
});
