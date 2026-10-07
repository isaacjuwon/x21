<?php

declare(strict_types=1);

use App\Enums\Plans\ServiceType;
use App\Enums\Wallets\WalletType;
use App\Http\Entities\ServiceResponse;
use App\Integrations\Contracts\Providers\VtuProvider;
use App\Managers\ApiManager;
use App\Models\Brand;
use App\Models\Plan;
use App\Models\TopupTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('api.providers.failover.providers', ['vtugate', 'vtpass']);
    Http::preventStrayRequests();
});

function mockVtuMethod(string $method, ServiceResponse $response): void
{
    $vtu = Mockery::mock(VtuProvider::class);
    $vtu->shouldReceive($method)->once()->andReturn($response);

    $manager = Mockery::mock(ApiManager::class);
    $manager->shouldReceive('vtuProvider')->andReturn($vtu);

    app()->instance(ApiManager::class, $manager);
}

test('GET /api/v1/services/plans groups brands and unified plans by service type', function () {
    $user = User::factory()->create();
    $user->deposit(100000, WalletType::General);

    $mtn = Brand::factory()->create(['name' => 'MTN', 'slug' => 'mtn', 'api_code' => 'MTN', 'status' => true]);
    $dstv = Brand::factory()->create(['name' => 'DSTV', 'slug' => 'dstv', 'api_code' => 'DSTV', 'status' => true]);
    $waec = Brand::factory()->create(['name' => 'WAEC', 'slug' => 'waec', 'api_code' => 'WAEC', 'status' => true]);

    $airtimePlan = Plan::create([
        'brand_id' => $mtn->id,
        'service_type' => ServiceType::Airtime,
        'name' => 'MTN VTU',
        'api_code' => 'MTN_VTU',
        'status' => true,
    ]);

    $dataPlan = Plan::create([
        'brand_id' => $mtn->id,
        'service_type' => ServiceType::Data,
        'name' => 'MTN 1GB',
        'api_code' => 'MTN_1GB',
        'price' => 500.00,
        'status' => true,
    ]);

    $cablePlan = Plan::create([
        'brand_id' => $dstv->id,
        'service_type' => ServiceType::Cable,
        'name' => 'DSTV Premium',
        'api_code' => 'DSTV_PREMIUM',
        'price' => 20000.00,
        'status' => true,
    ]);

    $eduPlan = Plan::create([
        'brand_id' => $waec->id,
        'service_type' => ServiceType::Education,
        'name' => 'WAEC Result Checker',
        'api_code' => 'WAEC_PIN',
        'price' => 3500.00,
        'status' => true,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/services/plans');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'airtime',
                'data',
                'cable',
                'electricity',
                'education',
            ],
        ]);

    $data = $response->json('data');
    expect($data['airtime'])->not->toBeEmpty()
        ->and($data['data'])->not->toBeEmpty()
        ->and($data['cable'])->not->toBeEmpty()
        ->and($data['education'])->not->toBeEmpty();
});

test('POST /api/v1/services/airtime creates transaction with unified Plan model', function () {
    $user = User::factory()->create();
    $user->deposit(10000, WalletType::General);

    $brand = Brand::factory()->create(['name' => 'MTN', 'slug' => 'mtn', 'api_code' => 'MTN', 'status' => true]);
    $plan = Plan::create([
        'brand_id' => $brand->id,
        'service_type' => ServiceType::Airtime,
        'name' => 'MTN Airtime',
        'status' => true,
    ]);
    $plan->setProviderCode('vtugate', 'MTN_AIRTIME');

    mockVtuMethod('purchaseAirtime', new ServiceResponse(code: 101, description: ['ref' => 'REF123', 'response_description' => 'Success']));

    $response = $this->actingAs($user, 'sanctum')
        ->withHeader('Idempotency-Key', 'test-key-'.uniqid())
        ->postJson('/api/v1/services/airtime', [
            'brand_id' => $brand->id,
            'phone_number' => '08012345678',
            'amount' => 500,
        ]);

    $response->assertStatus(201);

    $topup = TopupTransaction::latest()->first();
    expect($topup->plan_type)->toBe(Plan::class)
        ->and($topup->plan_id)->toBe($plan->id)
        ->and($topup->plan)->toBeInstanceOf(Plan::class);
});

test('POST /api/v1/services/data creates transaction with unified Plan model', function () {
    $user = User::factory()->create();
    $user->deposit(10000, WalletType::General);

    $brand = Brand::factory()->create(['name' => 'MTN', 'slug' => 'mtn', 'api_code' => 'MTN', 'status' => true]);
    $plan = Plan::create([
        'brand_id' => $brand->id,
        'service_type' => ServiceType::Data,
        'name' => 'MTN 1GB Data',
        'price' => 300,
        'status' => true,
    ]);
    $plan->setProviderCode('vtugate', 'MTN_1GB');

    mockVtuMethod('purchaseData', new ServiceResponse(code: 101, description: ['ref' => 'REF123', 'response_description' => 'Success']));

    $response = $this->actingAs($user, 'sanctum')
        ->withHeader('Idempotency-Key', 'test-key-'.uniqid())
        ->postJson('/api/v1/services/data', [
            'brand_id' => $brand->id,
            'plan_id' => $plan->id,
            'phone_number' => '08012345678',
        ]);

    $response->assertStatus(201);

    $topup = TopupTransaction::latest()->first();
    expect($topup->plan_type)->toBe(Plan::class)
        ->and($topup->plan_id)->toBe($plan->id)
        ->and($topup->plan)->toBeInstanceOf(Plan::class);
});

test('POST /api/v1/services/cable-tv creates transaction with unified Plan model', function () {
    $user = User::factory()->create();
    $user->deposit(20000, WalletType::General);

    $brand = Brand::factory()->create(['name' => 'DSTV', 'slug' => 'dstv', 'api_code' => 'DSTV', 'status' => true]);
    $plan = Plan::create([
        'brand_id' => $brand->id,
        'service_type' => ServiceType::Cable,
        'name' => 'DSTV Compact',
        'price' => 12000,
        'status' => true,
    ]);
    $plan->setProviderCode('vtugate', 'DSTV_COMPACT');

    mockVtuMethod('purchaseCable', new ServiceResponse(code: 101, description: ['ref' => 'REF123', 'response_description' => 'Success']));

    $response = $this->actingAs($user, 'sanctum')
        ->withHeader('Idempotency-Key', 'test-key-'.uniqid())
        ->postJson('/api/v1/services/cable-tv', [
            'brand_id' => $brand->id,
            'plan_id' => $plan->id,
            'smart_card_number' => '1234567890',
        ]);

    $response->assertStatus(201);

    $topup = TopupTransaction::latest()->first();
    expect($topup->plan_type)->toBe(Plan::class)
        ->and($topup->plan_id)->toBe($plan->id)
        ->and($topup->plan)->toBeInstanceOf(Plan::class);
});

test('POST /api/v1/services/electricity creates transaction with unified Plan model', function () {
    $user = User::factory()->create();
    $user->deposit(20000, WalletType::General);

    $brand = Brand::factory()->create(['name' => 'IKEDC', 'slug' => 'ikedc', 'api_code' => 'IKEDC', 'status' => true]);
    $plan = Plan::create([
        'brand_id' => $brand->id,
        'service_type' => ServiceType::Electricity,
        'name' => 'IKEDC Prepaid',
        'status' => true,
    ]);
    $plan->setProviderCode('vtugate', 'IKEDC_PREPAID');

    mockVtuMethod('purchaseElectricity', new ServiceResponse(code: 101, description: ['ref' => 'REF123', 'response_description' => 'Success']));

    $response = $this->actingAs($user, 'sanctum')
        ->withHeader('Idempotency-Key', 'test-key-'.uniqid())
        ->postJson('/api/v1/services/electricity', [
            'brand_id' => $brand->id,
            'meter_number' => '12345678901',
            'meter_type' => 'Prepaid',
            'amount' => 2000,
        ]);

    $response->assertStatus(201);

    $topup = TopupTransaction::latest()->first();
    expect($topup->plan_type)->toBe(Plan::class)
        ->and($topup->plan_id)->toBe($plan->id)
        ->and($topup->plan)->toBeInstanceOf(Plan::class);
});

test('POST /api/v1/services/education creates transaction with unified Plan model', function () {
    $user = User::factory()->create();
    $user->deposit(20000, WalletType::General);

    $brand = Brand::factory()->create(['name' => 'WAEC', 'slug' => 'waec', 'api_code' => 'WAEC', 'status' => true]);
    $plan = Plan::create([
        'brand_id' => $brand->id,
        'service_type' => ServiceType::Education,
        'name' => 'WAEC Result Checker',
        'price' => 3500,
        'status' => true,
    ]);
    $plan->setProviderCode('vtugate', 'WAEC_PIN');

    mockVtuMethod('purchaseExam', new ServiceResponse(code: 101, description: ['ref' => 'REF123', 'response_description' => 'Success', 'Content' => 'PIN12345']));

    $response = $this->actingAs($user, 'sanctum')
        ->withHeader('Idempotency-Key', 'test-key-'.uniqid())
        ->postJson('/api/v1/services/education', [
            'brand_id' => $brand->id,
            'plan_id' => $plan->id,
            'quantity' => 1,
        ]);

    $response->assertStatus(201);

    $topup = TopupTransaction::latest()->first();
    expect($topup->plan_type)->toBe(Plan::class)
        ->and($topup->plan_id)->toBe($plan->id)
        ->and($topup->plan)->toBeInstanceOf(Plan::class);
});
