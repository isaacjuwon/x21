<?php

declare(strict_types=1);

use App\Integrations\Paystack\Entities\InitializePayment;
use App\Integrations\Paystack\Entities\PaymentResponse;
use App\Integrations\Paystack\Exceptions\PaystackException;
use App\Integrations\Paystack\PaystackConnector;
use Illuminate\Support\Facades\Http;

test('can initialize payment', function () {
    Http::fake([
        'api.paystack.co/transaction/initialize' => Http::response([
            'status' => true,
            'message' => 'Authorization URL created',
            'data' => [
                'authorization_url' => 'https://checkout.paystack.com/test123',
                'access_code' => 'test_access_code',
                'reference' => 'test_reference',
                'status' => 'pending',
                'amount' => 50000,
            ],
        ], 200),
    ]);

    $paystack = app(PaystackConnector::class);

    $response = $paystack->transactions()->initialize(
        new InitializePayment(
            email: 'test@example.com',
            amount: 50000,
            reference: 'test_reference',
        )
    );

    expect($response)
        ->toBeInstanceOf(PaymentResponse::class)
        ->reference->toBe('test_reference')
        ->status->toBe('pending')
        ->amount->toBe(50000);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.paystack.co/transaction/initialize'
            && $request->method() === 'POST'
            && $request['email'] === 'test@example.com'
            && $request['amount'] === 50000;
    });
});

test('can verify payment', function () {
    Http::fake([
        'api.paystack.co/transaction/verify/*' => Http::response([
            'status' => true,
            'message' => 'Verification successful',
            'data' => [
                'reference' => 'test_reference',
                'status' => 'success',
                'amount' => 50000,
                'authorization_url' => '',
            ],
        ], 200),
    ]);

    $paystack = app(PaystackConnector::class);

    $response = $paystack->transactions()->verify('test_reference');

    expect($response)
        ->toBeInstanceOf(PaymentResponse::class)
        ->reference->toBe('test_reference')
        ->status->toBe('success')
        ->amount->toBe(50000);
});

test('throws exception when payment initialization fails', function () {
    Http::fake([
        'api.paystack.co/transaction/initialize' => Http::response([
            'status' => false,
            'message' => 'Invalid email',
        ], 400),
    ]);

    $paystack = app(PaystackConnector::class);

    $paystack->transactions()->initialize(
        new InitializePayment(
            email: 'invalid-email',
            amount: 50000,
        )
    );
})->throws(PaystackException::class);
