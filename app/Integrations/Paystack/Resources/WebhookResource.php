<?php

declare(strict_types=1);

namespace App\Integrations\Paystack\Resources;

use App\Integrations\Paystack\Entities\WebhookPayload;
use App\Integrations\Paystack\Exceptions\WebhookVerificationException;
use App\Integrations\Paystack\PaystackConnector;

final readonly class WebhookResource
{
    public function __construct(
        private PaystackConnector $connector,
    ) {}

    public function verify(string $signature, string $payload): bool
    {
        $hash = hash_hmac(
            algo: 'sha512',
            data: $payload,
            key: config('services.paystack.secret_key'),
        );

        if ($hash !== $signature) {
            throw new WebhookVerificationException('Invalid webhook signature');
        }

        return true;
    }

    public function parse(array $data): WebhookPayload
    {
        return WebhookPayload::fromWebhook($data);
    }
}
