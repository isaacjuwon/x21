<?php

declare(strict_types=1);

namespace Tests\Feature\Integrations\Paystack;

use App\Integrations\Paystack\Entities\InitializePayment;
use App\Integrations\Paystack\Entities\PaymentResponse;
use App\Integrations\Paystack\Exceptions\PaystackException;
use App\Integrations\Paystack\PaystackConnector;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TransactionResourceTest extends TestCase
{
    public function test_can_initialize_payment(): void
    {
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

        $this->assertInstanceOf(PaymentResponse::class, $response);
        $this->assertEquals('test_reference', $response->reference);
        $this->assertEquals('pending', $response->status);
        $this->assertEquals(50000, $response->amount);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.paystack.co/transaction/initialize'
                && $request->method() === 'POST'
                && $request['email'] === 'test@example.com'
                && $request['amount'] === 50000;
        });
    }

    public function test_can_verify_payment(): void
    {
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

        $this->assertInstanceOf(PaymentResponse::class, $response);
        $this->assertEquals('test_reference', $response->reference);
        $this->assertEquals('success', $response->status);
        $this->assertEquals(50000, $response->amount);
    }

    public function test_throws_exception_when_payment_initialization_fails(): void
    {
        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => false,
                'message' => 'Invalid email',
            ], 400),
        ]);

        $this->expectException(PaystackException::class);

        $paystack = app(PaystackConnector::class);

        $paystack->transactions()->initialize(
            new InitializePayment(
                email: 'invalid-email',
                amount: 50000,
            )
        );
    }
}
