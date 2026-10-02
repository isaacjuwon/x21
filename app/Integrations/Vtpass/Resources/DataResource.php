<?php

declare(strict_types=1);

namespace App\Integrations\Vtpass\Resources;

use App\Enums\Http\Method;
use App\Integrations\Epins\Entities\PurchaseData;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Integrations\Vtpass\Exceptions\VtpassException;
use App\Integrations\Vtpass\VtpassConnector;
use Throwable;

final readonly class DataResource
{
    public function __construct(
        private VtpassConnector $connector,
    ) {}

    public function purchase(PurchaseData $entity): ServiceResponse
    {
        $network = strtolower(trim($entity->network));
        $serviceId = match ($network) {
            '9mobile', 'etisalat', '9mobile-data', 'etisalat-data' => 'etisalat-data',
            'mtn', 'mtn-data' => 'mtn-data',
            'glo', 'glo-data' => 'glo-data',
            'airtel', 'airtel-data' => 'airtel-data',
            default => str_ends_with($network, '-data') ? $network : "{$network}-data",
        };

        try {
            $response = $this->connector->send(
                method: Method::Post,
                uri: '/pay',
                options: [
                    'request_id' => $entity->reference ?? $this->connector->generateRequestId(),
                    'serviceID' => $serviceId,
                    'billersCode' => $entity->mobileNumber,
                    'variation_code' => $entity->dataCode,
                    'phone' => $entity->mobileNumber,
                ],
            );
        } catch (Throwable $exception) {
            throw new VtpassException(
                message: 'Failed to purchase data via VTPass: '.$exception->getMessage(),
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
