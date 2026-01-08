<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Resources;

use App\Enums\Http\Method;
use App\Integrations\Epins\Entities\PurchaseCable;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Integrations\Epins\Entities\ValidateSmartcard;
use App\Integrations\Epins\Entities\ValidationResponse;
use App\Integrations\Epins\EpinsConnector;
use App\Integrations\Epins\Exceptions\EpinsException;
use Throwable;

final readonly class CableResource
{
    public function __construct(
        private EpinsConnector $connector,
    ) {}

    public function validate(ValidateSmartcard $entity): ValidationResponse
    {
        try {
            $response = $this->connector->send(
                method: Method::POST,
                uri: '/merchant-verify/',
                options: $entity->toRequestBody(),
            );
        } catch (Throwable $exception) {
            throw new EpinsException(
                message: 'Failed to validate smartcard: ' . $exception->getMessage(),
                previous: $exception,
            );
        }

        return ValidationResponse::fromResponse($response->json());
    }

    public function purchase(PurchaseCable $entity): ServiceResponse
    {
        try {
            $response = $this->connector->send(
                method: Method::POST,
                uri: '/biller/',
                options: $entity->toRequestBody(),
            );
        } catch (Throwable $exception) {
            throw new EpinsException(
                message: 'Failed to purchase cable subscription: ' . $exception->getMessage(),
                previous: $exception,
            );
        }

        return ServiceResponse::fromResponse($response->json());
    }
}
