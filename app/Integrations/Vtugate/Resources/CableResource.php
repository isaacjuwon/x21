<?php

declare(strict_types=1);

namespace App\Integrations\Vtugate\Resources;

use App\Enums\Http\Method;
use App\Integrations\Epins\Entities\PurchaseCable;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Integrations\Epins\Entities\ValidateSmartcard;
use App\Integrations\Epins\Entities\ValidationResponse;
use App\Integrations\Vtugate\Exceptions\VtugateException;
use App\Integrations\Vtugate\VtugateConnector;
use Throwable;

final readonly class CableResource
{
    public function __construct(
        private VtugateConnector $connector,
    ) {}

    public function validate(ValidateSmartcard $entity): ValidationResponse
    {
        try {
            $response = $this->connector->send(
                method: Method::Post,
                uri: '/api/v1/cable/verify',
                options: [
                    'service_id' => $entity->service,
                    'smartcard_number' => $entity->smartcardNumber,
                ],
            );
        } catch (Throwable $exception) {
            throw new VtugateException(
                message: 'Failed to validate smartcard via VtuGate: '.$exception->getMessage(),
                previous: $exception,
            );
        }

        $data = $response->json() ?? [];
        $isSuccess = ($data['status'] ?? null) === true;

        return new ValidationResponse(
            code: $isSuccess ? 119 : (int) ($data['code'] ?? 100),
            description: is_array($data['data'] ?? null) ? $data['data'] : ['message' => $data['message'] ?? 'Smartcard validation failed'],
        );
    }

    public function purchase(PurchaseCable $entity): ServiceResponse
    {
        $bouquet = $entity->apiCode;

        $body = [
            'service_id' => $entity->service,
            'smartcard_number' => $entity->smartcardNumber,
            'bouquet' => $bouquet,
            'amount' => $entity->amount,
            'phone' => $entity->smartcardNumber,
        ];

        if ($entity->reference !== null) {
            $body['ref'] = $entity->reference;
        }

        try {
            $response = $this->connector->send(
                method: Method::Post,
                uri: '/api/v1/cable/buy',
                options: $body,
            );
        } catch (Throwable $exception) {
            throw new VtugateException(
                message: 'Failed to purchase cable subscription via VtuGate: '.$exception->getMessage(),
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
                'content' => $data,
                'raw' => $data,
            ],
        );
    }
}
