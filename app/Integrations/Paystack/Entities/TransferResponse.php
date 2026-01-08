<?php

declare(strict_types=1);

namespace App\Integrations\Paystack\Entities;

final readonly class TransferResponse
{
    public function __construct(
        public string $reference,
        public string $status,
        public int $amount,
        public string $recipient,
        public ?string $transferCode = null,
        public ?string $reason = null,
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            reference: $data['reference'],
            status: $data['status'],
            amount: $data['amount'],
            recipient: $data['recipient'],
            transferCode: $data['transfer_code'] ?? null,
            reason: $data['reason'] ?? null,
        );
    }
}
