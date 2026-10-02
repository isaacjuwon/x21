<?php

declare(strict_types=1);

namespace App\Integrations\Vtugate\Resources;

use App\Enums\Http\Method;
use App\Integrations\Epins\Entities\PurchaseData;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Integrations\Vtugate\Exceptions\VtugateException;
use App\Integrations\Vtugate\VtugateConnector;
use Throwable;

final readonly class DataResource
{
    public function __construct(
        private VtugateConnector $connector,
    ) {}

    public function purchase(PurchaseData $entity): ServiceResponse
    {
        $network = strtolower(trim($entity->network));
        $network = match ($network) {
            '9mobile', 'etisalat', '9mobile-data', 'etisalat-data' => 'etisalat-data',
            'mtn', 'mtn-data' => 'mtn-data',
            'glo', 'glo-data' => 'glo-data',
            'airtel', 'airtel-data' => 'airtel-data',
            default => str_ends_with($network, '-data') ? $network : "{$network}-data",
        };

        $plan = $entity->apiCode;

        $body = [
            'network' => $network,
            'phone' => $entity->mobileNumber,
            'plan' => $plan,
        ];

        if ($entity->reference !== null) {
            $body['ref'] = $entity->reference;
        }

        try {
            $response = $this->connector->send(
                method: Method::Post,
                uri: '/api/v1/data/buy',
                options: $body,
            );
        } catch (Throwable $exception) {
            throw new VtugateException(
                message: 'Failed to purchase data via VtuGate: '.$exception->getMessage(),
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
