<?php

declare(strict_types=1);

namespace App\Integrations\Paystack\Resources;

use App\Enums\Http\Method;
use App\Integrations\Paystack\Entities\InitializePayment;
use App\Integrations\Paystack\Entities\PaymentResponse;
use App\Integrations\Paystack\Exceptions\PaystackException;
use App\Integrations\Paystack\PaystackConnector;
use Throwable;

final readonly class TransactionResource
{
    public function __construct(
        private PaystackConnector $connector,
    ) {}

    public function initialize(InitializePayment $entity): PaymentResponse
    {
        try {
            $response = $this->connector->send(
                method: Method::POST,
                uri: '/transaction/initialize',
                options: ['json' => $entity->toRequestBody()],
            );
        } catch (Throwable $exception) {
            throw new PaystackException(
                message: 'Failed to initialize payment: ' . $exception->getMessage(),
                previous: $exception,
            );
        }

        return PaymentResponse::fromResponse($response->json('data'));
    }

    public function verify(string $reference): PaymentResponse
    {
        try {
            $response = $this->connector->send(
                method: Method::GET,
                uri: "/transaction/verify/{$reference}",
            );
        } catch (Throwable $exception) {
            throw new PaystackException(
                message: 'Failed to verify payment: ' . $exception->getMessage(),
                previous: $exception,
            );
        }

        return PaymentResponse::fromResponse($response->json('data'));
    }
}
