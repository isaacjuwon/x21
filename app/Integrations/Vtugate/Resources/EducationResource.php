<?php

declare(strict_types=1);

namespace App\Integrations\Vtugate\Resources;

use App\Enums\Http\Method;
use App\Integrations\Epins\Entities\PurchaseExam;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Integrations\Vtugate\Exceptions\VtugateException;
use App\Integrations\Vtugate\VtugateConnector;
use Throwable;

final readonly class EducationResource
{
    public function __construct(
        private VtugateConnector $connector,
    ) {}

    public function purchase(PurchaseExam $entity): ServiceResponse
    {
        $variation = $entity->apiCode;

        $body = [
            'service_id' => $entity->service,
            'quantity' => $entity->numberOfPins,
            'variation' => $variation,
            'amount' => $entity->amount,
        ];

        try {
            $response = $this->connector->send(
                method: Method::Post,
                uri: '/api/v1/education/buy',
                options: $body,
            );
        } catch (Throwable $exception) {
            throw new VtugateException(
                message: 'Failed to purchase exam PIN via VtuGate: '.$exception->getMessage(),
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
                'pins' => $data['cards'] ?? $data['purchased_code'] ?? null,
                'content' => $data,
                'raw' => $data,
            ],
        );
    }
}
