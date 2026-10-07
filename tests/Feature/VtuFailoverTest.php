<?php

declare(strict_types=1);

use App\Http\Entities\PurchaseAirtime;
use App\Http\Entities\PurchaseData;
use App\Http\Entities\PurchaseElectricity;
use App\Http\Entities\ServiceResponse;
use App\Http\Entities\ValidateMeter;
use App\Http\Entities\ValidateSmartcard;
use App\Http\Entities\ValidationResponse;
use App\Integrations\Contracts\Providers\VtuProvider;
use App\Integrations\Failover\FailoverVtuProvider;
use App\Integrations\Vtpass\VtpassProvider;
use App\Integrations\Vtugate\Exceptions\VtugateException;
use App\Integrations\Vtugate\VtugateProvider;
use App\Managers\ApiManager;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('api manager resolves vtpass provider implementing VtuProvider', function () {
    $manager = app(ApiManager::class);
    $provider = $manager->vtuProvider('vtpass');

    expect($provider)->toBeInstanceOf(VtuProvider::class)
        ->and($provider)->toBeInstanceOf(VtpassProvider::class);
});

test('api manager resolves failover provider as default vtu provider', function () {
    $manager = app(ApiManager::class);
    $provider = $manager->vtuProvider();

    expect($provider)->toBeInstanceOf(VtuProvider::class)
        ->and($provider)->toBeInstanceOf(FailoverVtuProvider::class);
});

test('failover provider uses primary when primary succeeds', function () {
    $primary = Mockery::mock(VtuProvider::class);
    $secondary = Mockery::mock(VtuProvider::class);

    $entity = new PurchaseAirtime(
        network: 'mtn',
        amount: 500,
        mobileNumber: '08012345678',
        reference: 'REF-123',
    );

    $expectedResponse = new ServiceResponse(code: 101, description: ['ref' => 'REF-123']);

    $primary->shouldReceive('purchaseAirtime')
        ->once()
        ->with($entity)
        ->andReturn($expectedResponse);

    $secondary->shouldNotReceive('purchaseAirtime');

    $failover = new FailoverVtuProvider(
        providers: ['primary' => $primary, 'secondary' => $secondary],
        retryAfter: 60,
    );

    $response = $failover->purchaseAirtime($entity);

    expect($response->isSuccessful())->toBeTrue()
        ->and($response->description['ref'])->toBe('REF-123');
});

test('failover provider falls back to secondary when primary throws exception', function () {
    $primary = Mockery::mock(VtuProvider::class);
    $secondary = Mockery::mock(VtuProvider::class);

    $entity = new PurchaseAirtime(
        network: 'mtn',
        amount: 500,
        mobileNumber: '08012345678',
        reference: 'REF-123',
    );

    $primary->shouldReceive('purchaseAirtime')
        ->once()
        ->with($entity)
        ->andThrow(new VtugateException('Connection timeout to Vtugate'));

    $secondaryResponse = new ServiceResponse(code: 101, description: ['ref' => 'REF-VTPASS-123']);

    $secondary->shouldReceive('purchaseAirtime')
        ->once()
        ->with($entity)
        ->andReturn($secondaryResponse);

    $failover = new FailoverVtuProvider(
        providers: ['vtugate' => $primary, 'vtpass' => $secondary],
        retryAfter: 60,
    );

    $response = $failover->purchaseAirtime($entity);

    expect($response->isSuccessful())->toBeTrue()
        ->and($response->description['ref'])->toBe('REF-VTPASS-123')
        ->and($failover->isProviderDead('vtugate'))->toBeTrue()
        ->and($failover->isProviderDead('vtpass'))->toBeFalse();
});

test('failover provider falls back to secondary when primary returns unsuccessful response', function () {
    $primary = Mockery::mock(VtuProvider::class);
    $secondary = Mockery::mock(VtuProvider::class);

    $entity = new PurchaseData(
        network: 'mtn',
        mobileNumber: '08012345678',
        apiCode: '1000',
        reference: 'REF-DATA-1',
    );

    // Primary returns unsuccessful (e.g. insufficient provider balance)
    $primaryResponse = new ServiceResponse(code: 102, description: ['message' => 'Insufficient wallet balance']);

    $primary->shouldReceive('purchaseData')
        ->once()
        ->with($entity)
        ->andReturn($primaryResponse);

    $secondaryResponse = new ServiceResponse(code: 101, description: ['ref' => 'REF-DATA-1', 'message' => 'Delivered']);

    $secondary->shouldReceive('purchaseData')
        ->once()
        ->with($entity)
        ->andReturn($secondaryResponse);

    $failover = new FailoverVtuProvider(
        providers: ['vtugate' => $primary, 'vtpass' => $secondary],
        retryAfter: 60,
        failoverOnUnsuccessful: true,
    );

    $response = $failover->purchaseData($entity);

    expect($response->isSuccessful())->toBeTrue()
        ->and($failover->isProviderDead('vtugate'))->toBeTrue();
});

test('dead provider is bypassed on subsequent calls within retry period', function () {
    $primary = Mockery::mock(VtuProvider::class);
    $secondary = Mockery::mock(VtuProvider::class);

    $entity = new PurchaseAirtime(
        network: 'glo',
        amount: 200,
        mobileNumber: '08055555555',
        reference: 'REF-GLO-1',
    );

    $secondaryResponse = new ServiceResponse(code: 101, description: ['ref' => 'REF-GLO-1']);

    // Secondary is called directly because primary is marked dead
    $primary->shouldNotReceive('purchaseAirtime');
    $secondary->shouldReceive('purchaseAirtime')
        ->once()
        ->with($entity)
        ->andReturn($secondaryResponse);

    $failover = new FailoverVtuProvider(
        providers: ['vtugate' => $primary, 'vtpass' => $secondary],
        retryAfter: 60,
    );

    $failover->markProviderAsDead('vtugate');

    $response = $failover->purchaseAirtime($entity);

    expect($response->isSuccessful())->toBeTrue();
});

test('all providers failing returns last unsuccessful response or throws exception', function () {
    $primary = Mockery::mock(VtuProvider::class);
    $secondary = Mockery::mock(VtuProvider::class);

    $entity = new ValidateSmartcard(
        service: 'dstv',
        smartcardNumber: '1234567890',
    );

    $primary->shouldReceive('validateSmartcard')
        ->once()
        ->andThrow(new RuntimeException('Network error'));

    $secondary->shouldReceive('validateSmartcard')
        ->once()
        ->andThrow(new RuntimeException('VTPass also down'));

    $failover = new FailoverVtuProvider(
        providers: ['vtugate' => $primary, 'vtpass' => $secondary],
        retryAfter: 60,
    );

    expect(fn () => $failover->validateSmartcard($entity))
        ->toThrow(RuntimeException::class, 'VTPass also down');
});

test('vtpass provider airtime purchase sends correct request and returns ServiceResponse', function () {
    Http::fake([
        'https://vtpass.com/api/pay' => Http::response([
            'code' => '000',
            'response_description' => 'TRANSACTION SUCCESSFUL',
            'requestId' => 'REQ-VT-12345',
            'content' => [
                'transactions' => [
                    'status' => 'delivered',
                    'transactionId' => 'TX-999',
                ],
            ],
        ], 200),
    ]);

    $provider = app(ApiManager::class)->vtuProvider('vtpass');

    $entity = new PurchaseAirtime(
        network: 'mtn',
        amount: 500,
        mobileNumber: '08012345678',
        reference: 'REQ-VT-12345',
    );

    $response = $provider->purchaseAirtime($entity);

    expect($response)->toBeInstanceOf(ServiceResponse::class)
        ->and($response->isSuccessful())->toBeTrue()
        ->and($response->description['ref'])->toBe('REQ-VT-12345')
        ->and($response->description['response_description'])->toBe('TRANSACTION SUCCESSFUL');
});

test('vtugate provider resolves through api manager', function () {
    $manager = app(ApiManager::class);
    $provider = $manager->vtuProvider('vtugate');
    expect($provider)->toBeInstanceOf(VtuProvider::class)
        ->and($provider)->toBeInstanceOf(VtugateProvider::class);
});

test('vtugate provider airtime purchase sends form-urlencoded and returns ServiceResponse', function () {
    Http::fake([
        'https://api.vtugate.com/api/v1/airtime' => Http::response([
            'status' => true,
            'message' => 'Airtime sent successfully',
            'ref' => 'VTG-12345',
            'data' => ['id' => 999],
        ], 200),
    ]);
    $provider = app(ApiManager::class)->vtuProvider('vtugate');
    $entity = new PurchaseAirtime(network: 'mtn', amount: 500, mobileNumber: '08012345678', reference: 'REF-AIR-VTG');
    $response = $provider->purchaseAirtime($entity);
    expect($response)->toBeInstanceOf(ServiceResponse::class)
        ->and($response->isSuccessful())->toBeTrue()
        ->and($response->description['response_description'])->toBe('Airtime sent successfully');
    Http::assertSent(function (Request $req) {
        return $req->isForm();
    });
});

test('vtpass provider meter validation sends correct request and returns ValidationResponse', function () {
    Http::fake([
        'https://vtpass.com/api/merchant-verify' => Http::response([
            'code' => '000',
            'content' => [
                'Customer_Name' => 'JANE DOE',
                'Meter_Number' => '12345678901',
                'Customer_Account_Type' => 'prepaid',
            ],
        ], 200),
    ]);

    $provider = app(ApiManager::class)->vtuProvider('vtpass');

    $entity = new ValidateMeter(
        service: 'ikeja-electric',
        meterNumber: '12345678901',
        meterType: 'prepaid',
    );

    $response = $provider->validateMeter($entity);

    expect($response)->toBeInstanceOf(ValidationResponse::class)
        ->and($response->isValid())->toBeTrue()
        ->and($response->description['Customer_Name'])->toBe('JANE DOE');
});

test('vtpass provider electricity purchase includes token in response', function () {
    Http::fake([
        'https://vtpass.com/api/pay' => Http::response([
            'code' => '000',
            'response_description' => 'TRANSACTION SUCCESSFUL',
            'requestId' => 'REQ-ELEC-123',
            'purchased_code' => 'Token : 1234-5678-9012-3456-7890',
            'content' => [
                'transactions' => [
                    'status' => 'delivered',
                ],
            ],
        ], 200),
    ]);

    $provider = app(ApiManager::class)->vtuProvider('vtpass');

    $entity = new PurchaseElectricity(
        service: 'ikeja-electric',
        meterNumber: '12345678901',
        meterType: 'prepaid',
        apiCode: 'prepaid',
        amount: 2000,
        reference: 'REQ-ELEC-123',
    );

    $response = $provider->purchaseElectricity($entity);

    expect($response)->toBeInstanceOf(ServiceResponse::class)
        ->and($response->isSuccessful())->toBeTrue()
        ->and($response->description['token'])->toBe('Token : 1234-5678-9012-3456-7890');
});

test('failover automatically resets dead providers if all providers are currently dead', function () {
    $primary = Mockery::mock(VtuProvider::class);
    $secondary = Mockery::mock(VtuProvider::class);

    $entity = new PurchaseAirtime(
        network: 'mtn',
        amount: 100,
        mobileNumber: '08012345678',
        reference: 'REF-RETRY-ALL',
    );

    // Primary will be tried again after auto-reset and succeeds
    $primary->shouldReceive('purchaseAirtime')
        ->once()
        ->with($entity)
        ->andReturn(new ServiceResponse(code: 101, description: ['ref' => 'REF-RETRY-ALL']));

    $failover = new FailoverVtuProvider(
        providers: ['vtugate' => $primary, 'vtpass' => $secondary],
        retryAfter: 60,
    );

    // Mark both as dead
    $failover->markProviderAsDead('vtugate');
    $failover->markProviderAsDead('vtpass');

    $response = $failover->purchaseAirtime($entity);

    expect($response->isSuccessful())->toBeTrue();
});

test('vtpass provider passes apiCode as variation_code', function () {
    Http::fake([
        'https://vtpass.com/api/pay' => function (Request $request) {
            $data = $request->data();
            expect($data['variation_code'])->toBe('DATA-PLAN-001');

            return Http::response([
                'code' => '000',
                'response_description' => 'TRANSACTION SUCCESSFUL',
                'requestId' => 'REQ-DATA-PREF',
                'content' => ['transactions' => ['status' => 'delivered']],
            ], 200);
        },
    ]);

    $provider = app(ApiManager::class)->vtuProvider('vtpass');

    $entity = new PurchaseData(
        network: 'mtn',
        mobileNumber: '08012345678',
        apiCode: 'DATA-PLAN-001',
        reference: 'REQ-DATA-PREF',
    );

    $response = $provider->purchaseData($entity);

    expect($response)->toBeInstanceOf(ServiceResponse::class)
        ->and($response->isSuccessful())->toBeTrue();
});
