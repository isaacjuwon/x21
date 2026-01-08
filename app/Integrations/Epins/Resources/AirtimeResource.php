<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Resources;

use App\Enums\Http\Method;
use App\Integrations\Epins\Entities\PurchaseAirtime;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Integrations\Epins\EpinsConnector;
use App\Integrations\Epins\Exceptions\EpinsException;
use Throwable;

final readonly class AirtimeResource
{
    public function __construct(
        private EpinsConnector $connector,
    ) {}

    public function purchase(PurchaseAirtime $entity): ServiceResponse
    {
        try {
            $response = $this->connector->send(
                method: Method::POST,
                uri: '/airtime/',
                options: $entity->toRequestBody(),
            );
        } catch (Throwable $exception) {
            throw new EpinsException(
                message: 'Failed to purchase airtime: ' . $exception->getMessage(),
                previous: $exception,
            );
        }

        return ServiceResponse::fromResponse($response->json());
    }
}
