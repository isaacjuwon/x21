<?php

declare(strict_types=1);

namespace App\Integrations\Paystack\Resources;

use App\Enums\Http\Method;
use App\Integrations\Paystack\Entities\CreateRecipient;
use App\Integrations\Paystack\Entities\RecipientResponse;
use App\Integrations\Paystack\Exceptions\PaystackException;
use App\Integrations\Paystack\PaystackConnector;
use Throwable;

final readonly class RecipientResource
{
    public function __construct(
        private PaystackConnector $connector,
    ) {}

    public function create(CreateRecipient $entity): RecipientResponse
    {
        try {
            $response = $this->connector->send(
                method: Method::POST,
                uri: '/transferrecipient',
                options: $entity->toRequestBody(),
            );
        } catch (Throwable $exception) {
            throw new PaystackException(
                message: 'Failed to create transfer recipient: '.$exception->getMessage(),
                previous: $exception,
            );
        }

        return RecipientResponse::fromResponse($response->json('data'));
    }
}
