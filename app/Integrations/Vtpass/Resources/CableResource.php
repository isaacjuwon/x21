<?php

declare(strict_types=1);

namespace App\Integrations\Vtpass\Resources;

use App\Enums\Http\Method;
use App\Http\Entities\PurchaseCable;
use App\Http\Entities\ServiceResponse;
use App\Http\Entities\ValidateSmartcard;
use App\Http\Entities\ValidationResponse;
use App\Integrations\Vtpass\Exceptions\VtpassException;
use App\Integrations\Vtpass\VtpassConnector;
use Throwable;

final readonly class CableResource
{
    public function __construct(
        private VtpassConnector $connector,
    ) {}

    public function validate(ValidateSmartcard $entity): ValidationResponse
    {
        try {
            $response = $this->connector->send(
                method: Method::Post,
                uri: '/merchant-verify',
                options: [
                    'serviceID' => $entity->service,
                    'billersCode' => $entity->smartcardNumber,
                ],
            );
        } catch (Throwable $exception) {
            throw new VtpassException(
                message: 'Failed to validate smartcard via VTPass: '.$exception->getMessage(),
                previous: $exception,
            );
        }

        $data = $response->json() ?? [];
        $isSuccess = ($data['code'] ?? null) === '000';

        return new ValidationResponse(
            code: $isSuccess ? 119 : (int) ($data['code'] ?? 100),
            description: is_array($data['content'] ?? null) ? $data['content'] : ['message' => $data['response_description'] ?? 'Smartcard validation failed'],
        );
    }

    public function purchase(PurchaseCable $entity): ServiceResponse
    {
        try {
            $response = $this->connector->send(
                method: Method::Post,
                uri: '/pay',
                options: [
                    'request_id' => $entity->reference ?? $this->connector->generateRequestId(),
                    'serviceID' => $entity->service,
                    'billersCode' => $entity->smartcardNumber,
                    'variation_code' => $entity->apiCode,
                    'amount' => $entity->amount,
                    'phone' => $entity->smartcardNumber,
                ],
            );
        } catch (Throwable $exception) {
            throw new VtpassException(
                message: 'Failed to purchase cable subscription via VTPass: '.$exception->getMessage(),
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
                'content' => $data['content'] ?? [],
                'raw' => $data,
            ],
        );
    }
}
