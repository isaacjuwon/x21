<?php

declare(strict_types=1);

namespace App\Integrations\Vtpass\Resources;

use App\Enums\Http\Method;
use App\Integrations\Epins\Entities\PurchaseElectricity;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Integrations\Epins\Entities\ValidateMeter;
use App\Integrations\Epins\Entities\ValidationResponse;
use App\Integrations\Vtpass\Exceptions\VtpassException;
use App\Integrations\Vtpass\VtpassConnector;
use Throwable;

final readonly class ElectricityResource
{
    public function __construct(
        private VtpassConnector $connector,
    ) {}

    public function validateMeter(ValidateMeter $entity): ValidationResponse
    {
        try {
            $response = $this->connector->send(
                method: Method::Post,
                uri: '/merchant-verify',
                options: [
                    'serviceID' => $entity->service,
                    'billersCode' => $entity->meterNumber,
                    'type' => strtolower($entity->meterType),
                ],
            );
        } catch (Throwable $exception) {
            throw new VtpassException(
                message: 'Failed to validate meter via VTPass: '.$exception->getMessage(),
                previous: $exception,
            );
        }

        $data = $response->json() ?? [];
        $isSuccess = ($data['code'] ?? null) === '000';

        return new ValidationResponse(
            code: $isSuccess ? 119 : (int) ($data['code'] ?? 100),
            description: is_array($data['content'] ?? null) ? $data['content'] : ['message' => $data['response_description'] ?? 'Meter validation failed'],
        );
    }

    public function purchase(PurchaseElectricity $entity): ServiceResponse
    {
        try {
            $response = $this->connector->send(
                method: Method::Post,
                uri: '/pay',
                options: [
                    'request_id' => $entity->reference ?? $this->connector->generateRequestId(),
                    'serviceID' => $entity->service,
                    'billersCode' => $entity->meterNumber,
                    'variation_code' => $entity->vtpassCode ?: strtolower($entity->meterType),
                    'amount' => $entity->amount,
                    'phone' => $entity->meterNumber,
                ],
            );
        } catch (Throwable $exception) {
            throw new VtpassException(
                message: 'Failed to purchase electricity via VTPass: '.$exception->getMessage(),
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
                'token' => $data['purchased_code'] ?? $data['token'] ?? null,
                'content' => $data['content'] ?? [],
                'raw' => $data,
            ],
        );
    }
}
