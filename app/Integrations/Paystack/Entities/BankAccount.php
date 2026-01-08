<?php

declare(strict_types=1);

namespace App\Integrations\Paystack\Entities;

final readonly class BankAccount
{
    public function __construct(
        public string $accountNumber,
        public string $accountName,
        public int $bankId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            accountNumber: $data['account_number'],
            accountName: $data['account_name'],
            bankId: $data['bank_id'],
        );
    }
}
