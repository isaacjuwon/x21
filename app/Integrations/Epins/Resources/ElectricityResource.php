<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Resources;

use App\Enums\Http\Method;
use App\Integrations\Epins\Entities\PurchaseElectricity;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Integrations\Epins\Entities\ValidateMeter;
use App\Integrations\Epins\Entities\ValidationResponse;
use App\Integrations\Epins\EpinsConnector;
use App\Integrations\Epins\Exceptions\EpinsException;
use Throwable;

final readonly class ElectricityResource
{
    public function __construct(
        private EpinsConnector $connector,
    ) {}

    public function validateMeter(ValidateMeter $entity): ValidationResponse
    {
        try {
            $response = $this->connector->send(
                method: Method::POST,
                uri: '/merchant-verify/',
                options: $entity->toRequestBody(),
            );
        } catch (Throwable $exception) {
            throw new EpinsException(
                message: 'Failed to validate meter: ' . $exception->getMessage(),
                previous: $exception,
            );
        }

        return ValidationResponse::fromResponse($response->json());
    }

    public function purchase(PurchaseElectricity $entity): ServiceResponse
    {
        try {
            $response = $this->connector->send(
                method: Method::POST,
                uri: '/biller/',
                options: $entity->toRequestBody(),
            );
        } catch (Throwable $exception) {
            throw new EpinsException(
                message: 'Failed to purchase electricity: ' . $exception->getMessage(),
                previous: $exception,
            );
        }

        return ServiceResponse::fromResponse($response->json());
    }
}
