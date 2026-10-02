<?php

use App\Actions\Vtu\PurchaseDataAction;
use App\Enums\Topups\TopupTransactionStatus;
use App\Enums\Topups\TopupType;
use App\Integrations\Contracts\Providers\VtuProvider;
use App\Integrations\Epins\Entities\PurchaseData as PurchaseDataEntity;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Managers\ApiManager;
use App\Models\Brand;
use App\Models\DataPlan;
use App\Models\TopupTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    config()->set('api.providers.failover.providers', ['vtugate', 'vtpass']);

    Bus::fake();
    Event::fake();
});

function makeDataTransaction(DataPlan $plan, Brand $brand): TopupTransaction
{
    return TopupTransaction::factory()->for(User::factory())->create([
        'plan_id' => $plan->id,
        'plan_type' => DataPlan::class,
        'brand_id' => $brand->id,
        'type' => TopupType::Data,
        'status' => TopupTransactionStatus::Pending,
        'recipient' => '08012345678',
        'meta' => ['network' => $brand->api_code],
    ]);
}

function bindMockProvider(callable $capture): void
{
    $providerMock = Mockery::mock(VtuProvider::class);

    $providerMock->shouldReceive('purchaseData')
        ->once()
        ->with(Mockery::type(PurchaseDataEntity::class))
        ->andReturnUsing(function (PurchaseDataEntity $entity) use ($capture) {
            $capture($entity);

            return new ServiceResponse(code: 101, description: ['ref' => 'MOCKED-REF', 'response_description' => 'Delivered']);
        });

    $managerMock = Mockery::mock(ApiManager::class);
    $managerMock->shouldReceive('vtuProvider')->andReturn($providerMock);

    app()->instance(ApiManager::class, $managerMock);
}

test('PurchaseDataAction resolves vtugate code only when relationship has vtugate row', function () {
    $brand = Brand::factory()->create(['api_code' => 'mtn']);
    $plan = DataPlan::factory()->for($brand)->create();
    $plan->providerCodes()->create([
        'provider' => 'vtugate',
        'code' => 'VTG_MTN_1GB',
    ]);
    $plan->load('providerCodes');

    $capturedEntity = null;
    bindMockProvider(function (PurchaseDataEntity $entity) use (&$capturedEntity) {
        $capturedEntity = $entity;
    });

    $transaction = makeDataTransaction($plan, $brand);

    $response = app(PurchaseDataAction::class)->handle($transaction);

    expect($response->isSuccessful())->toBeTrue();
    expect($capturedEntity)->not->toBeNull();
    expect($capturedEntity->apiCode)->toBe('VTG_MTN_1GB');
    expect($capturedEntity->network)->toBe('mtn');
    expect($capturedEntity->mobileNumber)->toBe('08012345678');
});

test('PurchaseDataAction throws RuntimeException when plan has zero provider rows — no legacy fallback reuse', function () {
    $brand = Brand::factory()->create(['api_code' => 'glo']);
    $plan = DataPlan::factory()->for($brand)->create([
        'api_code' => 'OLD_LEGACY_DEAD_CODE_SHOULD_NOT_BE_USED',
    ]);
    $transaction = makeDataTransaction($plan, $brand);

    expect(fn () => app(PurchaseDataAction::class)->handle($transaction))
        ->toThrow(RuntimeException::class);

    $transaction->refresh();
    expect($transaction->status)->toBe(TopupTransactionStatus::Pending);
});

test('PurchaseDataAction falls back to vtpass code via resolveApiCode when vtugate row missing but vtpass row present', function () {
    $brand = Brand::factory()->create(['api_code' => 'airtel']);
    $plan = DataPlan::factory()->for($brand)->create();
    $plan->providerCodes()->create([
        'provider' => 'vtpass',
        'code' => 'airtel-5gb-2500',
    ]);
    $plan->load('providerCodes');

    $capturedEntity = null;
    bindMockProvider(function (PurchaseDataEntity $entity) use (&$capturedEntity) {
        $capturedEntity = $entity;
    });

    $transaction = makeDataTransaction($plan, $brand);

    app(PurchaseDataAction::class)->handle($transaction);

    expect($capturedEntity)->not->toBeNull();
    expect($capturedEntity->apiCode)->toBe('airtel-5gb-2500');
});
