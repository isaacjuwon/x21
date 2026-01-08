<?php

declare(strict_types=1);

namespace App\Integrations\Paystack\Entities;

final readonly class PaymentResponse
{
    public function __construct(
        public string $reference,
        public string $status,
        public int $amount,
        public string $authorizationUrl,
        public ?string $accessCode = null,
        public ?array $metadata = null,
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            reference: $data['reference'],
            status: $data['status'],
            amount: $data['amount'],
            authorizationUrl: $data['authorization_url'] ?? '',
            accessCode: $data['access_code'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }
}
