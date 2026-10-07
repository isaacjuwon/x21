<?php

declare(strict_types=1);

namespace App\Integrations\Vtugate\Resources;

use App\Enums\Http\Method;
use App\Http\Entities\PurchaseElectricity;
use App\Http\Entities\ServiceResponse;
use App\Http\Entities\ValidateMeter;
use App\Http\Entities\ValidationResponse;
use App\Integrations\Vtugate\Exceptions\VtugateException;
use App\Integrations\Vtugate\VtugateConnector;
use Throwable;

final readonly class ElectricityResource
{
    public function __construct(
        private VtugateConnector $connector,
    ) {}

    public function validateMeter(ValidateMeter $entity): ValidationResponse
    {
        try {
            $response = $this->connector->send(
                method: Method::Post,
                uri: '/api/v1/electricity/verify',
                options: [
                    'service_id' => $entity->service,
                    'meter_number' => $entity->meterNumber,
                    'meter_type' => strtolower($entity->meterType),
                ],
            );
        } catch (Throwable $exception) {
            throw new VtugateException(
                message: 'Failed to validate meter via VtuGate: '.$exception->getMessage(),
                previous: $exception,
            );
        }

        $data = $response->json() ?? [];
        $isSuccess = ($data['status'] ?? null) === true;

        return new ValidationResponse(
            code: $isSuccess ? 119 : (int) ($data['code'] ?? 100),
            description: is_array($data['data'] ?? null) ? $data['data'] : ['message' => $data['message'] ?? 'Meter validation failed'],
        );
    }

    public function purchase(PurchaseElectricity $entity): ServiceResponse
    {
        $variation = $entity->apiCode;

        $body = [
            'service_id' => $entity->service,
            'meter_number' => $entity->meterNumber,
            'meter_type' => strtolower($entity->meterType),
            'variation' => $variation,
            'amount' => $entity->amount,
            'phone' => $entity->meterNumber,
        ];

        if ($entity->reference !== null) {
            $body['ref'] = $entity->reference;
        }

        try {
            $response = $this->connector->send(
                method: Method::Post,
                uri: '/api/v1/electricity/buy',
                options: $body,
            );
        } catch (Throwable $exception) {
            throw new VtugateException(
                message: 'Failed to purchase electricity via VtuGate: '.$exception->getMessage(),
                previous: $exception,
            );
        }

        $data = $response->json() ?? [];
        $isSuccess = ($data['status'] ?? null) === true;

        return new ServiceResponse(
            code: $isSuccess ? 101 : (int) ($data['code'] ?? 102),
            description: [
                'ref' => ($entity->reference ?? '').($data['ref'] ?? ''),
                'response_description' => $data['message'] ?? ($isSuccess ? 'Transaction Successful' : 'Transaction Failed'),
                'token' => $data['purchased_code'] ?? $data['token'] ?? null,
                'content' => $data,
                'raw' => $data,
            ],
        );
    }
}
