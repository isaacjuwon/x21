<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Resources;

use App\Enums\Http\Method;
use App\Integrations\Epins\Entities\PurchaseExam;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Integrations\Epins\EpinsConnector;
use App\Integrations\Epins\Exceptions\EpinsException;
use Throwable;

final readonly class EducationResource
{
    public function __construct(
        private EpinsConnector $connector,
    ) {}

    public function purchase(PurchaseExam $entity): ServiceResponse
    {
        try {
            $response = $this->connector->send(
                method: Method::POST,
                uri: '/exams/',
                options: $entity->toRequestBody(),
            );
        } catch (Throwable $exception) {
            throw new EpinsException(
                message: 'Failed to purchase exam PIN: ' . $exception->getMessage(),
                previous: $exception,
            );
        }

        return ServiceResponse::fromResponse($response->json());
    }
}
