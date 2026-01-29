<?php

declare(strict_types=1);

namespace App\Http\Payloads\Wallet;

final readonly class WithdrawPayload
{
    public function __construct(
        public float $amount,
        public string $accountNumber,
        public string $bankCode,
        public string $reference,
    ) {}

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'account_number' => $this->accountNumber,
            'bank_code' => $this->bankCode,
            'reference' => $this->reference,
        ];
    }
}
