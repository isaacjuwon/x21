<?php

declare(strict_types=1);

namespace App\Integrations\Vtpass\Resources;

use App\Enums\Http\Method;
use App\Integrations\Epins\Entities\PurchaseExam;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Integrations\Vtpass\Exceptions\VtpassException;
use App\Integrations\Vtpass\VtpassConnector;
use Throwable;

final readonly class EducationResource
{
    public function __construct(
        private VtpassConnector $connector,
    ) {}

    public function purchase(PurchaseExam $entity): ServiceResponse
    {
        try {
            $response = $this->connector->send(
                method: Method::Post,
                uri: '/pay',
                options: [
                    'request_id' => $entity->reference ?? $this->connector->generateRequestId(),
                    'serviceID' => $entity->service,
                    'variation_code' => $entity->vtpassCode ?: $entity->variationCode,
                    'amount' => $entity->amount,
                    'quantity' => $entity->numberOfPins,
                ],
            );
        } catch (Throwable $exception) {
            throw new VtpassException(
                message: 'Failed to purchase exam PIN via VTPass: '.$exception->getMessage(),
                previous: $exception,
            );
        }

        $data = $response->json() ?? [];
        $isSuccess = ($data['code'] ?? null) === '000';

        return new ServiceResponse(
            code: $isSuccess ? 101 : (int) ($data['code'] ?? 102),
            description: [
                'ref' => $data['requestId'] ?? $entity->reference,
                'response_description' => $data['response_description'] ?? ($isSuccess ? 'Transaction Successful' : 'Transaction Failed'),
                'pins' => $data['cards'] ?? $data['purchased_code'] ?? null,
                'content' => $data['content'] ?? [],
                'raw' => $data,
            ],
        );
    }
}
