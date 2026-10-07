<?php

declare(strict_types=1);

use App\Enums\Plans\ServiceType;
use App\Models\Brand;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('can create a unified plan with generic fields and backed enum service_type', function () {
    $brand = Brand::factory()->create(['name' => 'MTN Nigeria']);

    $plan = Plan::create([
        'brand_id' => $brand->id,
        'service_type' => ServiceType::Data,
        'name' => 'MTN 1GB SME',
        'type' => 'SME',
        'api_code' => 'MTN_SME_1GB',
        'price' => 250.00,
        'cost_price' => 220.00,
        'duration' => '30 Days',
        'status' => true,
    ]);

    expect($plan->exists)->toBeTrue()
        ->and($plan->service_type)->toBe(ServiceType::Data)
        ->and($plan->service_type->value)->toBe('data')
        ->and($plan->service_type->getLabel())->toBe('Data Bundle')
        ->and($plan->price)->toBe('250.00')
        ->and($plan->cost_price)->toBe('220.00')
        ->and($plan->profitMargin())->toBe(30.00)
        ->and($plan->isAvailable())->toBeTrue()
        ->and($plan->brand->id)->toBe($brand->id);
});

it('resolves provider codes via HasProviderCodes on unified plan', function () {
    $brand = Brand::factory()->create();

    $plan = Plan::factory()->create([
        'brand_id' => $brand->id,
        'api_code' => 'DEFAULT_CODE',
    ]);

    $plan->providerCodes()->create([
        'provider' => 'vtpass',
        'code' => 'VTPASS_CODE_123',
    ]);

    $plan->providerCodes()->create([
        'provider' => 'vtugate',
        'code' => 'VTUGATE_CODE_456',
    ]);

    $plan->load('providerCodes');

    expect($plan->apiCodeFor('vtpass'))->toBe('VTPASS_CODE_123')
        ->and($plan->apiCodeFor('vtugate'))->toBe('VTUGATE_CODE_456')
        ->and($plan->apiCodeFor('unknown'))->toBeNull();
});

it('supports scopes for active and service types', function () {
    Plan::factory()->create(['service_type' => 'data', 'status' => true]);
    Plan::factory()->create(['service_type' => 'data', 'status' => false]);
    Plan::factory()->create(['service_type' => 'airtime', 'status' => true]);

    expect(Plan::active()->count())->toBe(2)
        ->and(Plan::forService('data')->count())->toBe(2)
        ->and(Plan::active()->forService('data')->count())->toBe(1);
});
