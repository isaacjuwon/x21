<?php

declare(strict_types=1);

namespace App\Integrations\KudiSms\Resources;

use App\Enums\Http\Method;
use App\Integrations\KudiSms\Entities\SendSms;
use App\Integrations\KudiSms\Entities\SmsResponse;
use App\Integrations\KudiSms\Exceptions\KudiSmsException;
use App\Integrations\KudiSms\KudiSmsConnector;
use App\Jobs\RecordApiRequestJob;
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
                uri: '/api/sms',
                options: $entity->toRequestBody(),
            );
        } catch (Throwable $exception) {
            RecordApiRequestJob::dispatch(
                type: 'kudisms',
                method: Method::Post->value,
                url: '/api/sms',
                payload: $entity->toRequestBody(),
                response: ['error' => $exception->getMessage()],
            );

            throw new KudiSmsException(
                message: 'Failed to send SMS: '.$exception->getMessage(),
                previous: $exception,
            );
        }

        $jsonResponse = $response->json();

        RecordApiRequestJob::dispatch(
            type: 'kudisms',
            method: Method::Post->value,
            url: '/api/sms',
            payload: $entity->toRequestBody(),
            response: is_array($jsonResponse) ? $jsonResponse : $response->body(),
        );

        return SmsResponse::fromResponse($jsonResponse ?? []);
    }
}
