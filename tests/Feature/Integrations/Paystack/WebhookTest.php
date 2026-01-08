<?php

declare(strict_types=1);

use App\Events\Paystack\PaymentFailed;
use App\Events\Paystack\PaymentSuccessful;
use App\Events\Paystack\TransferFailed;
use App\Events\Paystack\TransferSuccessful;
use App\Integrations\Paystack\Exceptions\WebhookVerificationException;
use Illuminate\Support\Facades\Event;

test('webhook verifies signature correctly', function () {
    config(['services.paystack.secret_key' => 'test_secret_key']);

    $payload = json_encode([
        'event' => 'charge.success',
        'data' => [
            'reference' => 'test_ref',
            'amount' => 50000,
            'status' => 'success',
        ],
    ]);

    $signature = hash_hmac('sha512', $payload, 'test_secret_key');

    $response = $this->postJson('/webhooks/paystack', json_decode($payload, true), [
        'x-paystack-signature' => $signature,
    ]);

    $response->assertNoContent();
});

test('webhook rejects invalid signature', function () {
    config(['services.paystack.secret_key' => 'test_secret_key']);

    $payload = json_encode([
        'event' => 'charge.success',
        'data' => [
            'reference' => 'test_ref',
        ],
    ]);

    $response = $this->postJson('/webhooks/paystack', json_decode($payload, true), [
        'x-paystack-signature' => 'invalid_signature',
    ]);

    $response->assertStatus(500);
});

test('webhook dispatches payment successful event', function () {
    Event::fake();
    config(['services.paystack.secret_key' => 'test_secret_key']);

    $payload = json_encode([
        'event' => 'charge.success',
        'data' => [
            'reference' => 'test_ref',
            'amount' => 50000,
            'status' => 'success',
        ],
    ]);

    $signature = hash_hmac('sha512', $payload, 'test_secret_key');

    $this->postJson('/webhooks/paystack', json_decode($payload, true), [
        'x-paystack-signature' => $signature,
    ]);

    Event::assertDispatched(PaymentSuccessful::class, function ($event) {
        return $event->data['reference'] === 'test_ref';
    });
});

test('webhook dispatches payment failed event', function () {
    Event::fake();
    config(['services.paystack.secret_key' => 'test_secret_key']);

    $payload = json_encode([
        'event' => 'charge.failed',
        'data' => [
            'reference' => 'test_ref',
            'status' => 'failed',
        ],
    ]);

    $signature = hash_hmac('sha512', $payload, 'test_secret_key');

    $this->postJson('/webhooks/paystack', json_decode($payload, true), [
        'x-paystack-signature' => $signature,
    ]);

    Event::assertDispatched(PaymentFailed::class);
});

test('webhook dispatches transfer successful event', function () {
    Event::fake();
    config(['services.paystack.secret_key' => 'test_secret_key']);

    $payload = json_encode([
        'event' => 'transfer.success',
        'data' => [
            'reference' => 'transfer_ref',
            'status' => 'success',
        ],
    ]);

    $signature = hash_hmac('sha512', $payload, 'test_secret_key');

    $this->postJson('/webhooks/paystack', json_decode($payload, true), [
        'x-paystack-signature' => $signature,
    ]);

    Event::assertDispatched(TransferSuccessful::class);
});

test('webhook dispatches transfer failed event', function () {
    Event::fake();
    config(['services.paystack.secret_key' => 'test_secret_key']);

    $payload = json_encode([
        'event' => 'transfer.failed',
        'data' => [
            'reference' => 'transfer_ref',
            'status' => 'failed',
        ],
    ]);

    $signature = hash_hmac('sha512', $payload, 'test_secret_key');

    $this->postJson('/webhooks/paystack', json_decode($payload, true), [
        'x-paystack-signature' => $signature,
    ]);

    Event::assertDispatched(TransferFailed::class);
});

test('webhook ignores unknown events', function () {
    Event::fake();
    config(['services.paystack.secret_key' => 'test_secret_key']);

    $payload = json_encode([
        'event' => 'unknown.event',
        'data' => [],
    ]);

    $signature = hash_hmac('sha512', $payload, 'test_secret_key');

    $response = $this->postJson('/webhooks/paystack', json_decode($payload, true), [
        'x-paystack-signature' => $signature,
    ]);

    $response->assertNoContent();
    Event::assertNothingDispatched();
});
