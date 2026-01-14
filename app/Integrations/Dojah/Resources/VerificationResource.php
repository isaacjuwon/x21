<?php

declare(strict_types=1);

namespace App\Integrations\Dojah\Resources;

use App\Integrations\Dojah\DojahConnector;
use App\Integrations\Dojah\Entities\VerificationRequest;
use App\Integrations\Dojah\Entities\VerificationResponse;
use App\Integrations\Dojah\Exceptions\DojahException;
use Throwable;

final readonly class VerificationResource
{
    public function __construct(
        private DojahConnector $connector,
    ) {}

    public function verify(VerificationRequest $entity): VerificationResponse
    {
        try {
            $response = $this->connector->send(
                method: 'post',
                uri: '/api/v1/kyc/verify',
                options: ['json' => $entity->toRequestBody()],
            );
        } catch (Throwable $exception) {
            throw new DojahException(
                message: 'Failed to verify identity: ' . $exception->getMessage(),
                previous: $exception,
            );
        }

        return VerificationResponse::fromResponse($response->json());
    }
}
