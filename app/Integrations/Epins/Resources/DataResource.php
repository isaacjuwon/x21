<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Resources;

use App\Enums\Http\Method;
use App\Integrations\Epins\Entities\PurchaseData;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Integrations\Epins\EpinsConnector;
use App\Integrations\Epins\Exceptions\EpinsException;
use Throwable;

final readonly class DataResource
{
    public function __construct(
        private EpinsConnector $connector,
    ) {}

    public function purchase(PurchaseData $entity): ServiceResponse
    {
        try {
            $response = $this->connector->send(
                method: Method::POST,
                uri: '/data/',
                options: $entity->toRequestBody(),
            );
        } catch (Throwable $exception) {
            throw new EpinsException(
                message: 'Failed to purchase data: ' . $exception->getMessage(),
                previous: $exception,
            );
        }

        return ServiceResponse::fromResponse($response->json());
    }
}
