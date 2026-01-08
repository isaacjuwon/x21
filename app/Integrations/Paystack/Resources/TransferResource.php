<?php

declare(strict_types=1);

namespace App\Integrations\Paystack\Resources;

use App\Enums\Http\Method;
use App\Integrations\Paystack\Entities\InitiateTransfer;
use App\Integrations\Paystack\Entities\TransferResponse;
use App\Integrations\Paystack\Exceptions\PaystackException;
use App\Integrations\Paystack\PaystackConnector;
use Throwable;

final readonly class TransferResource
{
    public function __construct(
        private PaystackConnector $connector,
    ) {}

    public function initiate(InitiateTransfer $entity): TransferResponse
    {
        try {
            $response = $this->connector->send(
                method: Method::POST,
                uri: '/transfer',
                options: $entity->toRequestBody(),
            );
        } catch (Throwable $exception) {
            throw new PaystackException(
                message: 'Failed to initiate transfer: ' . $exception->getMessage(),
                previous: $exception,
            );
        }

        return TransferResponse::fromResponse($response->json('data'));
    }

    public function verify(string $reference): TransferResponse
    {
        try {
            $response = $this->connector->send(
                method: Method::GET,
                uri: "/transfer/verify/{$reference}",
            );
        } catch (Throwable $exception) {
            throw new PaystackException(
                message: 'Failed to verify transfer: ' . $exception->getMessage(),
                previous: $exception,
            );
        }

        return TransferResponse::fromResponse($response->json('data'));
    }
}
