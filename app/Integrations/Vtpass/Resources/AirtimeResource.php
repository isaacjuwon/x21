<?php

declare(strict_types=1);

namespace App\Integrations\Vtpass\Resources;

use App\Enums\Http\Method;
use App\Integrations\Epins\Entities\PurchaseAirtime;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Integrations\Vtpass\Exceptions\VtpassException;
use App\Integrations\Vtpass\VtpassConnector;
use Throwable;

final readonly class AirtimeResource
{
    public function __construct(
        private VtpassConnector $connector,
    ) {}

    public function purchase(PurchaseAirtime $entity): ServiceResponse
    {
        $network = strtolower(trim($entity->network));
        $serviceId = match ($network) {
            '9mobile', 'etisalat' => 'etisalat',
            'mtn' => 'mtn',
            'glo' => 'glo',
            'airtel' => 'airtel',
            default => $network,
        };

        try {
            $response = $this->connector->send(
                method: Method::Post,
                uri: '/pay',
                options: [
                    'request_id' => $entity->reference ?? $this->connector->generateRequestId(),
                    'serviceID' => $serviceId,
                    'amount' => $entity->amount,
                    'phone' => $entity->mobileNumber,
                ],
            );
        } catch (Throwable $exception) {
            throw new VtpassException(
                message: 'Failed to purchase airtime via VTPass: '.$exception->getMessage(),
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
