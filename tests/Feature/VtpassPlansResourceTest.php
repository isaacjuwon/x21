<?php

declare(strict_types=1);

use App\Integrations\Vtpass\Resources\PlansResource;
use App\Integrations\Vtpass\VtpassConnector;
use App\Integrations\Vtpass\VtpassProvider;
use App\Managers\ApiManager;
use Illuminate\Support\Facades\Http;

test('vtpass connector and provider expose PlansResource', function () {
    $connector = app(VtpassConnector::class);
    expect($connector->plans())->toBeInstanceOf(PlansResource::class);

    /** @var VtpassProvider $provider */
    $provider = app(ApiManager::class)->vtuProvider('vtpass');
    expect($provider->plans())->toBeInstanceOf(PlansResource::class);
});

test('vtpass plans resource can fetch service categories', function () {
    Http::fake([
        '*/service-categories' => Http::response([
            'response_description' => '000',
            'content' => [
                ['identifier' => 'airtime', 'name' => 'Airtime and Data'],
                ['identifier' => 'tv-subscription', 'name' => 'TV Subscription'],
            ],
        ], 200),
    ]);

    /** @var VtpassConnector $connector */
    $connector = app(VtpassConnector::class);
    $categories = $connector->plans()->fetchCategories();

    expect($categories['response_description'])->toBe('000')
        ->and($categories['content'])->toHaveCount(2);
});

test('vtpass plans resource can fetch services filtered by category', function () {
    Http::fake([
        '*/services?identifier=data*' => Http::response([
            'response_description' => '000',
            'content' => [
                ['serviceID' => 'mtn-data', 'name' => 'MTN Data'],
                ['serviceID' => 'airtel-data', 'name' => 'Airtel Data'],
            ],
        ], 200),
    ]);

    /** @var VtpassConnector $connector */
    $connector = app(VtpassConnector::class);
    $services = $connector->plans()->fetchServices('data');

    expect($services['response_description'])->toBe('000')
        ->and($services['content'][0]['serviceID'])->toBe('mtn-data');
});

test('vtpass plans resource can fetch variations for a single service', function () {
    Http::fake([
        '*/service-variations?serviceID=mtn-data*' => Http::response([
            'response_description' => '000',
            'content' => [
                'ServiceName' => 'MTN Data',
                'serviceID' => 'mtn-data',
                'variations' => [
                    [
                        'variation_code' => 'mtn-10mb-100',
                        'name' => 'N100 100MB - 24 hrs',
                        'variation_amount' => '100.00',
                        'fixedPrice' => 'Yes',
                    ],
                ],
            ],
        ], 200),
    ]);

    /** @var VtpassConnector $connector */
    $connector = app(VtpassConnector::class);
    $variations = $connector->plans()->fetchVariations('mtn-data');

    expect($variations['content']['serviceID'])->toBe('mtn-data')
        ->and($variations['content']['variations'])->toHaveCount(1);
});

test('vtpass plans resource concurrently fetches variations across multiple services using Http pool', function () {
    Http::fake([
        '*/service-variations?serviceID=mtn-data*' => Http::response([
            'response_description' => '000',
            'content' => [
                'serviceID' => 'mtn-data',
                'variations' => [
                    ['variation_code' => 'mtn-1gb', 'name' => 'MTN 1GB', 'variation_amount' => '300.00'],
                ],
            ],
        ], 200),
        '*/service-variations?serviceID=airtel-data*' => Http::response([
            'response_description' => '000',
            'content' => [
                'serviceID' => 'airtel-data',
                'variations' => [
                    ['variation_code' => 'airtel-1gb', 'name' => 'Airtel 1GB', 'variation_amount' => '350.00'],
                ],
            ],
        ], 200),
        '*/service-variations?serviceID=glo-data*' => Http::response([
            'response_description' => '000',
            'content' => [
                'serviceID' => 'glo-data',
                'variations' => [
                    ['variation_code' => 'glo-1gb', 'name' => 'Glo 1GB', 'variation_amount' => '280.00'],
                ],
            ],
        ], 200),
    ]);

    /** @var VtpassConnector $connector */
    $connector = app(VtpassConnector::class);
    $batch = $connector->plans()->fetchVariationsPool(['mtn-data', 'airtel-data', 'glo-data']);

    expect($batch)->toHaveKeys(['mtn-data', 'airtel-data', 'glo-data'])
        ->and($batch['mtn-data']['content']['variations'][0]['variation_code'])->toBe('mtn-1gb')
        ->and($batch['airtel-data']['content']['variations'][0]['variation_code'])->toBe('airtel-1gb')
        ->and($batch['glo-data']['content']['variations'][0]['variation_code'])->toBe('glo-1gb');
});
