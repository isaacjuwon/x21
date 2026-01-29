<?php

declare(strict_types=1);

namespace App\Http\Payloads\Wallet;

final readonly class DepositPayload
{
    public function __construct(
        public float $amount,
    ) {}

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
        ];
    }
}
