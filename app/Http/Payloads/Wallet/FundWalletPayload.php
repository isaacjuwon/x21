<?php

declare(strict_types=1);

namespace App\Http\Payloads\Wallet;

final readonly class FundWalletPayload
{
    public function __construct(
        public float $amount,
        public string $email,
        public string $reference,
    ) {}

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'email' => $this->email,
            'reference' => $this->reference,
        ];
    }
}
