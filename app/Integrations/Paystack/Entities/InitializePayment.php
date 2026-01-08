<?php

declare(strict_types=1);

namespace App\Integrations\Paystack\Entities;

final readonly class InitializePayment
{
    public function __construct(
        public string $email,
        public float $amount,
        public ?string $reference = null,
        public ?string $callbackUrl = null,
        public ?array $metadata = null,
        public ?array $channels = null,
    ) {}

    public function toRequestBody(): array
    {
        return array_filter([
            'email' => $this->email,
            'amount' => (int) round($this->amount * 100), // Convert to kobo (smallest currency unit)
            'reference' => $this->reference,
            'callback_url' => $this->callbackUrl,
            'metadata' => $this->metadata,
            'channels' => $this->channels,
        ], fn ($value) => $value !== null);
    }
}
