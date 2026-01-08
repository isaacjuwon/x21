<?php

declare(strict_types=1);

namespace App\Integrations\Paystack\Entities;

final readonly class InitiateTransfer
{
    public function __construct(
        public string $source,
        public int $amount,
        public string $recipient,
        public ?string $reference = null,
        public ?string $reason = null,
    ) {}

    public function toRequestBody(): array
    {
        return array_filter([
            'source' => $this->source,
            'amount' => $this->amount,
            'recipient' => $this->recipient,
            'reference' => $this->reference,
            'reason' => $this->reason,
        ], fn ($value) => $value !== null);
    }
}
