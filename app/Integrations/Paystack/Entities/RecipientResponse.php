<?php

declare(strict_types=1);

namespace App\Integrations\Paystack\Entities;

final readonly class RecipientResponse
{
    public function __construct(
        public string $recipientCode,
        public string $name,
        public string $accountNumber,
        public string $bankCode,
        public int $id,
        public string $type,
        public bool $active,
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            recipientCode: $data['recipient_code'],
            name: $data['name'],
            accountNumber: $data['details']['account_number'],
            bankCode: $data['details']['bank_code'],
            id: $data['id'],
            type: $data['type'],
            active: $data['active'],
        );
    }
}
