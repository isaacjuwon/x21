<?php

declare(strict_types=1);

use App\Integrations\Paystack\Entities\InitiateTransfer;
use App\Integrations\Paystack\Entities\TransferResponse;
use App\Integrations\Paystack\Exceptions\PaystackException;
use App\Integrations\Paystack\PaystackConnector;
use Illuminate\Support\Facades\Http;

test('can initiate transfer', function () {
    Http::fake([
        'api.paystack.co/transfer' => Http::response([
            'status' => true,
            'message' => 'Transfer has been queued',
            'data' => [
                'reference' => 'transfer_ref_123',
                'status' => 'pending',
                'amount' => 100000,
                'recipient' => 'RCP_test123',
                'transfer_code' => 'TRF_test123',
                'reason' => 'Withdrawal request',
            ],
        ], 200),
    ]);

    $paystack = app(PaystackConnector::class);

    $response = $paystack->transfers()->initiate(
        new InitiateTransfer(
            source: 'balance',
            amount: 100000,
            recipient: 'RCP_test123',
            reason: 'Withdrawal request',
        )
    );

    expect($response)
        ->toBeInstanceOf(TransferResponse::class)
        ->reference->toBe('transfer_ref_123')
        ->status->toBe('pending')
        ->amount->toBe(100000)
        ->recipient->toBe('RCP_test123');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.paystack.co/transfer'
            && $request->method() === 'POST'
            && $request['source'] === 'balance'
            && $request['amount'] === 100000;
    });
});

test('can verify transfer', function () {
    Http::fake([
        'api.paystack.co/transfer/verify/*' => Http::response([
            'status' => true,
            'message' => 'Transfer retrieved',
            'data' => [
                'reference' => 'transfer_ref_123',
                'status' => 'success',
                'amount' => 100000,
                'recipient' => 'RCP_test123',
            ],
        ], 200),
    ]);

    $paystack = app(PaystackConnector::class);

    $response = $paystack->transfers()->verify('transfer_ref_123');

    expect($response)
        ->toBeInstanceOf(TransferResponse::class)
        ->reference->toBe('transfer_ref_123')
        ->status->toBe('success')
        ->amount->toBe(100000);
});

test('throws exception when transfer initiation fails', function () {
    Http::fake([
        'api.paystack.co/transfer' => Http::response([
            'status' => false,
            'message' => 'Invalid recipient',
        ], 400),
    ]);

    $paystack = app(PaystackConnector::class);

    $paystack->transfers()->initiate(
        new InitiateTransfer(
            source: 'balance',
            amount: 100000,
            recipient: 'invalid',
        )
    );
})->throws(PaystackException::class);
