<?php

declare(strict_types=1);

use App\DTOs\NormalizedPlan;
use App\Enums\Plans\ServiceType;
use App\Models\Plan;
use App\Models\PlanProviderCode;
use App\Services\Vtu\PlanSyncService;
use Illuminate\Support\Facades\Http;

test('persistNormalizedPlan creates new plan and attaches provider code', function () {
    $syncService = app(PlanSyncService::class);

    $normalized = new NormalizedPlan(
        provider: 'vtugate',
        providerCode: '101',
        brandSlug: 'mtn',
        serviceType: ServiceType::Data,
        name: 'MTN 1GB SME Data - 30 Days',
        type: 'SME',
        duration: '30 Days',
        costPrice: 280.0,
        canonicalKey: 'mtn_data_1gb_sme_30days',
        meta: ['volume' => '1GB'],
    );

    $result = $syncService->persistNormalizedPlan($normalized);

    expect($result['action'])->toBe('created')
        ->and(Plan::count())->toBe(1)
        ->and(PlanProviderCode::count())->toBe(1);

    /** @var Plan $plan */
    $plan = $result['plan'];
    expect($plan->name)->toBe('MTN 1GB SME Data - 30 Days')
        ->and($plan->service_type)->toBe(ServiceType::Data)
        ->and($plan->brand->slug)->toBe('mtn')
        ->and((float) $plan->cost_price)->toBe(280.0)
        ->and((float) $plan->price)->toBeGreaterThan(280.0)
        ->and($plan->apiCodeFor('vtugate'))->toBe('101');
});

test('persisting equivalent plan from different provider merges into single Plan model with multiple provider codes', function () {
    $syncService = app(PlanSyncService::class);

    // 1. First provider: VtuGate
    $vtugatePlan = new NormalizedPlan(
        provider: 'vtugate',
        providerCode: 'VTG_1GB',
        brandSlug: 'mtn',
        serviceType: ServiceType::Data,
        name: 'MTN 1GB SME Data - 30 Days',
        type: 'SME',
        duration: '30 Days',
        costPrice: 280.0,
        canonicalKey: 'mtn_data_1gb_sme_30days',
        meta: ['volume' => '1GB'],
    );

    $firstResult = $syncService->persistNormalizedPlan($vtugatePlan);
    expect($firstResult['action'])->toBe('created')
        ->and(Plan::count())->toBe(1);

    // 2. Second provider: VTPass for the same package
    $vtpassPlan = new NormalizedPlan(
        provider: 'vtpass',
        providerCode: 'mtn-sme-1gb',
        brandSlug: 'mtn',
        serviceType: ServiceType::Data,
        name: '1GB SME - 30 Days',
        type: 'SME',
        duration: '30 Days',
        costPrice: 285.0,
        canonicalKey: 'mtn_data_1gb_sme_30days',
        meta: ['volume' => '1GB'],
    );

    $secondResult = $syncService->persistNormalizedPlan($vtpassPlan);

    // Assert action was 'merged', no duplicate Plan was created, and both provider codes exist!
    expect($secondResult['action'])->toBe('merged')
        ->and(Plan::count())->toBe(1)
        ->and(PlanProviderCode::count())->toBe(2);

    $plan = Plan::first();
    expect($plan->id)->toBe($firstResult['plan']->id)
        ->and($plan->id)->toBe($secondResult['plan']->id)
        ->and($plan->apiCodeFor('vtugate'))->toBe('VTG_1GB')
        ->and($plan->apiCodeFor('vtpass'))->toBe('mtn-sme-1gb');
});

test('syncVtugate and syncVtpass fetch and populate plans end-to-end', function () {
    Http::fake([
        '*/api/v1/fetchdataplans' => Http::response([
            'status' => true,
            'data' => [
                'provider_status' => true,
                'data_plans' => [
                    ['code' => 'VTG_1', 'name' => 'MTN 1GB SME', 'price' => 280, 'duration' => '30 Days'],
                ],
            ],
        ], 200),
        '*/api/v1/fetchservices' => Http::response([
            'status' => true,
            'data' => [
                ['service_id' => 1, 'network_name' => 'MTN'],
            ],
        ], 200),
        '*/service-variations?serviceID=mtn-data*' => Http::response([
            'response_description' => '000',
            'content' => [
                'serviceID' => 'mtn-data',
                'variations' => [
                    ['variation_code' => 'VTP_1', 'name' => '1GB SME - 30 Days', 'variation_amount' => '282.00'],
                ],
            ],
        ], 200),
        '*/service-variations*' => Http::response([
            'response_description' => '000',
            'content' => ['variations' => []],
        ], 200),
    ]);

    /** @var PlanSyncService $syncService */
    $syncService = app(PlanSyncService::class);

    // Sync VtuGate
    $vtugateReport = $syncService->syncVtugate(ServiceType::Data);
    expect($vtugateReport->created)->toBe(1)
        ->and(Plan::count())->toBe(1);

    // Sync VTPass (should merge into the existing Plan!)
    $vtpassReport = $syncService->syncVtpass(ServiceType::Data);
    expect($vtpassReport->merged)->toBe(1)
        ->and(Plan::count())->toBe(1)
        ->and(PlanProviderCode::count())->toBe(2);

    $plan = Plan::first();
    expect($plan->apiCodeFor('vtugate'))->toBe('VTG_1')
        ->and($plan->apiCodeFor('vtpass'))->toBe('VTP_1');
});
