<?php

declare(strict_types=1);

namespace App\Http\Payloads\Wallet;

final readonly class TransferPayload
{
    public function __construct(
        public float $amount,
        public string $phoneNumber,
        public ?string $notes = null,
    ) {}

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'phone_number' => $this->phoneNumber,
            'notes' => $this->notes,
        ];
    }
}
