<?php

declare(strict_types=1);

namespace App\Integrations\Paystack\Entities;

final readonly class PaymentResponse
{
    public function __construct(
        public string $reference,
        public string $status,
        public float $amount,
        public string $authorizationUrl,
        public ?string $accessCode = null,
        public ?array $metadata = null,
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            reference: $data['reference'],
            status: $data['status'] ?? 'pending',
            amount: (float) ($data['amount'] ?? 0) / 100, // Convert from kobo/cents
            authorizationUrl: $data['authorization_url'] ?? '',
            accessCode: $data['access_code'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }
}
