<?php

declare(strict_types=1);

namespace Tests\Feature\Integrations\Paystack;

use App\Events\Paystack\PaymentFailed;
use App\Events\Paystack\PaymentSuccessful;
use App\Events\Paystack\TransferFailed;
use App\Events\Paystack\TransferSuccessful;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    public function test_webhook_verifies_signature_correctly(): void
    {
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
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
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
    }

    public function test_webhook_dispatches_payment_successful_event(): void
    {
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
    }

    public function test_webhook_dispatches_payment_failed_event(): void
    {
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
    }

    public function test_webhook_dispatches_transfer_successful_event(): void
    {
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
    }

    public function test_webhook_dispatches_transfer_failed_event(): void
    {
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
    }

    public function test_webhook_ignores_unknown_events(): void
    {
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
    }
}
