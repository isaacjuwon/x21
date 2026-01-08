<?php

declare(strict_types=1);

namespace App\Integrations\Paystack\Entities;

final readonly class WebhookPayload
{
    public function __construct(
        public string $event,
        public array $data,
    ) {}

    public static function fromWebhook(array $payload): self
    {
        return new self(
            event: $payload['event'],
            data: $payload['data'],
        );
    }
}
