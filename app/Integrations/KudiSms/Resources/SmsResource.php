<?php

declare(strict_types=1);

namespace App\Integrations\KudiSms\Resources;

use App\Enums\Http\Method;
use App\Integrations\KudiSms\Entities\SendSms;
use App\Integrations\KudiSms\Entities\SmsResponse;
use App\Integrations\KudiSms\Exceptions\KudiSmsException;
use App\Integrations\KudiSms\KudiSmsConnector;
use Throwable;

final readonly class SmsResource
{
    public function __construct(
        private KudiSmsConnector $connector,
    ) {}

    /**
     * @throws KudiSmsException
     */
    public function send(SendSms $entity): SmsResponse
    {
        try {
            $response = $this->connector->send(
                method: Method::Post,
                uri: '/api/v1/sms',
                options: $entity->toRequestBody(),
            );
        } catch (Throwable $exception) {
            throw new KudiSmsException(
                message: 'Failed to send SMS: '.$exception->getMessage(),
                previous: $exception,
            );
        }

        return SmsResponse::fromResponse($response->json());
    }
}
