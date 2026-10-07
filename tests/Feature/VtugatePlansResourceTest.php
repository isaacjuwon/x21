<?php

declare(strict_types=1);

use App\Integrations\Vtugate\Resources\PlansResource;
use App\Integrations\Vtugate\VtugateConnector;
use App\Integrations\Vtugate\VtugateProvider;
use App\Managers\ApiManager;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('vtugate connector and provider expose PlansResource', function () {
    $connector = app(VtugateConnector::class);
    expect($connector->plans())->toBeInstanceOf(PlansResource::class);

    /** @var VtugateProvider $provider */
    $provider = app(ApiManager::class)->vtuProvider('vtugate');
    expect($provider->plans())->toBeInstanceOf(PlansResource::class);
});

test('vtugate plans resource can fetch all services', function () {
    Http::fake([
        '*/api/v1/fetchallservices' => Http::response([
            'status' => true,
            'data' => [
                ['service_id' => 101, 'service_type' => 'airtime', 'network_name' => 'MTN'],
                ['service_id' => 102, 'service_type' => 'data', 'network_name' => 'MTN'],
                ['service_id' => 103, 'service_type' => 'tv', 'network_name' => 'DSTV'],
            ],
        ], 200),
    ]);

    /** @var VtugateConnector $connector */
    $connector = app(VtugateConnector::class);
    $response = $connector->plans()->fetchAllServices();

    expect($response['status'])->toBeTrue()
        ->and($response['data'])->toHaveCount(3);
});

test('vtugate plans resource can fetch services by type', function () {
    Http::fake([
        '*/api/v1/fetchservices' => function (Request $request) {
            expect($request->data()['service_type'])->toBe('data');

            return Http::response([
                'status' => true,
                'data' => [
                    ['service_id' => 1, 'network_name' => 'MTN Data'],
                    ['service_id' => 2, 'network_name' => 'Airtel Data'],
                ],
            ], 200);
        },
    ]);

    /** @var VtugateConnector $connector */
    $connector = app(VtugateConnector::class);
    $response = $connector->plans()->fetchServices('data');

    expect($response['status'])->toBeTrue()
        ->and($response['data'])->toHaveCount(2);
});

test('vtugate plans resource can fetch data plans for a single service', function () {
    Http::fake([
        '*/api/v1/fetchdataplans' => function (Request $request) {
            expect($request->data()['service_id'])->toBe(1);

            return Http::response([
                'status' => true,
                'message' => 'Data plans fetched successfully',
                'data' => [
                    'provider_status' => true,
                    'data_plans' => [
                        ['code' => '1', 'name' => 'MTN 1GB', 'price' => 280],
                        ['code' => '2', 'name' => 'MTN 2GB', 'price' => 560],
                    ],
                ],
            ], 200);
        },
    ]);

    /** @var VtugateConnector $connector */
    $connector = app(VtugateConnector::class);
    $response = $connector->plans()->fetchDataPlans(1);

    expect($response['status'])->toBeTrue()
        ->and($response['data']['data_plans'])->toHaveCount(2);
});

test('vtugate plans resource concurrently fetches data plans across multiple services using Http pool', function () {
    Http::fake([
        '*/api/v1/fetchdataplans' => function (Request $request) {
            $serviceId = (int) ($request->data()['service_id'] ?? 0);

            $plans = match ($serviceId) {
                1 => [['code' => 'mtn-1gb', 'price' => 280]],
                2 => [['code' => 'airtel-1gb', 'price' => 320]],
                3 => [['code' => 'glo-1gb', 'price' => 250]],
                default => [],
            };

            return Http::response([
                'status' => true,
                'data' => [
                    'data_plans' => $plans,
                ],
            ], 200);
        },
    ]);

    /** @var VtugateConnector $connector */
    $connector = app(VtugateConnector::class);
    $batch = $connector->plans()->fetchDataPlansPool([1, 2, 3]);

    expect($batch)->toHaveKeys(['1', '2', '3'])
        ->and($batch['1']['data']['data_plans'][0]['code'])->toBe('mtn-1gb')
        ->and($batch['2']['data']['data_plans'][0]['code'])->toBe('airtel-1gb')
        ->and($batch['3']['data']['data_plans'][0]['code'])->toBe('glo-1gb');
});

test('vtugate plans resource can fetch all data plans by discovering services first', function () {
    Http::fake([
        '*/api/v1/fetchservices' => Http::response([
            'status' => true,
            'data' => [
                ['service_id' => 10, 'network_name' => 'MTN'],
                ['service_id' => 20, 'network_name' => 'Airtel'],
            ],
        ], 200),
        '*/api/v1/fetchdataplans' => function (Request $request) {
            $serviceId = (int) $request->data()['service_id'];

            return Http::response([
                'status' => true,
                'data' => [
                    'data_plans' => [
                        ['code' => "plan-{$serviceId}", 'price' => 300],
                    ],
                ],
            ], 200);
        },
    ]);

    /** @var VtugateConnector $connector */
    $connector = app(VtugateConnector::class);
    $batch = $connector->plans()->fetchAllDataPlans();

    expect($batch)->toHaveKeys(['10', '20'])
        ->and($batch['10']['data']['data_plans'][0]['code'])->toBe('plan-10')
        ->and($batch['20']['data']['data_plans'][0]['code'])->toBe('plan-20');
});
