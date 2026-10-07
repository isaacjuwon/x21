<?php

declare(strict_types=1);

use App\DTOs\NormalizedPlan;
use App\Enums\Plans\ServiceType;
use App\Integrations\Vtpass\Normalizers\VtpassPlanNormalizer;
use App\Integrations\Vtugate\Normalizers\VtugatePlanNormalizer;

test('vtugate plan normalizer normalizes data plans into NormalizedPlan DTOs', function () {
    $normalizer = new VtugatePlanNormalizer;

    $raw = [
        'status' => true,
        'message' => 'Data plans fetched successfully',
        'data' => [
            'provider_status' => true,
            'data_plans' => [
                [
                    'code' => '1',
                    'name' => 'MTN 1GB SME',
                    'price' => 280,
                    'duration' => '30 Days',
                ],
                [
                    'code' => '2',
                    'name' => 'MTN 2GB (Gifting)',
                    'price' => 560,
                ],
            ],
        ],
    ];

    $plans = $normalizer->normalize($raw, [
        'service_type' => ServiceType::Data,
        'brand' => 'mtn',
    ]);

    expect($plans)->toHaveCount(2);

    /** @var NormalizedPlan $first */
    $first = $plans[0];
    expect($first->provider)->toBe('vtugate')
        ->and($first->providerCode)->toBe('1')
        ->and($first->brandSlug)->toBe('mtn')
        ->and($first->serviceType)->toBe(ServiceType::Data)
        ->and($first->type)->toBe('SME')
        ->and($first->duration)->toBe('30 Days')
        ->and($first->costPrice)->toBe(280.0)
        ->and($first->canonicalKey)->toBe('mtn_data_1gb_sme_30days');

    /** @var NormalizedPlan $second */
    $second = $plans[1];
    expect($second->providerCode)->toBe('2')
        ->and($second->type)->toBe('Gifting')
        ->and($second->canonicalKey)->toBe('mtn_data_2gb_gifting_30days');
});

test('vtpass plan normalizer normalizes service variations into NormalizedPlan DTOs', function () {
    $normalizer = new VtpassPlanNormalizer;

    $raw = [
        'response_description' => '000',
        'content' => [
            'ServiceName' => 'MTN Data',
            'serviceID' => 'mtn-data',
            'variations' => [
                [
                    'variation_code' => 'mtn-1gb-1000',
                    'name' => '1GB SME - 30 Days',
                    'variation_amount' => '285.00',
                    'fixedPrice' => 'Yes',
                ],
            ],
        ],
    ];

    $plans = $normalizer->normalize($raw, [
        'service_type' => ServiceType::Data,
        'service_id' => 'mtn-data',
    ]);

    expect($plans)->toHaveCount(1);

    /** @var NormalizedPlan $plan */
    $plan = $plans[0];
    expect($plan->provider)->toBe('vtpass')
        ->and($plan->providerCode)->toBe('mtn-1gb-1000')
        ->and($plan->brandSlug)->toBe('mtn')
        ->and($plan->serviceType)->toBe(ServiceType::Data)
        ->and($plan->type)->toBe('SME')
        ->and($plan->duration)->toBe('30 Days')
        ->and($plan->costPrice)->toBe(285.0)
        ->and($plan->canonicalKey)->toBe('mtn_data_1gb_sme_30days');
});

test('vtugate and vtpass normalizers produce identical canonical keys for equivalent packages', function () {
    $vtugateNormalizer = new VtugatePlanNormalizer;
    $vtpassNormalizer = new VtpassPlanNormalizer;

    $vtugateRaw = [
        'data' => [
            'data_plans' => [
                ['code' => '10', 'name' => 'MTN 1GB SME', 'price' => 280, 'duration' => '30 Days'],
            ],
        ],
    ];

    $vtpassRaw = [
        'content' => [
            'serviceID' => 'mtn-data',
            'variations' => [
                ['variation_code' => 'mtn-sme-1gb', 'name' => '1GB SME - 30 Days', 'variation_amount' => '285.00'],
            ],
        ],
    ];

    $vtugatePlans = $vtugateNormalizer->normalize($vtugateRaw, ['brand' => 'mtn', 'service_type' => ServiceType::Data]);
    $vtpassPlans = $vtpassNormalizer->normalize($vtpassRaw, ['service_type' => ServiceType::Data]);

    expect($vtugatePlans[0]->canonicalKey)->toBe($vtpassPlans[0]->canonicalKey)
        ->and($vtugatePlans[0]->canonicalKey)->toBe('mtn_data_1gb_sme_30days');
});
