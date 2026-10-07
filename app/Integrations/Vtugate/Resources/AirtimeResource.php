<?php

declare(strict_types=1);

namespace App\Integrations\Vtugate\Resources;

use App\Enums\Http\Method;
use App\Http\Entities\PurchaseAirtime;
use App\Http\Entities\ServiceResponse;
use App\Integrations\Vtugate\Exceptions\VtugateException;
use App\Integrations\Vtugate\VtugateConnector;
use Throwable;

final readonly class AirtimeResource
{
    public function __construct(
        private VtugateConnector $connector,
    ) {}

    public function purchase(PurchaseAirtime $entity): ServiceResponse
    {
        $network = strtolower(trim($entity->network));
        $network = match ($network) {
            '9mobile' => 'etisalat',
            default => $network,
        };

        $body = [
            'network' => $network,
            'phone' => $entity->mobileNumber,
            'amount' => $entity->amount,
        ];

        if ($entity->reference !== null) {
            $body['ref'] = $entity->reference;
        }

        try {
            $response = $this->connector->send(
                method: Method::Post,
                uri: '/api/v1/airtime',
                options: $body,
            );
        } catch (Throwable $exception) {
            throw new VtugateException(
                message: 'Failed to purchase airtime via VtuGate: '.$exception->getMessage(),
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
