<?php

declare(strict_types=1);

namespace App\Integrations\Paystack\Entities;

final readonly class CreateRecipient
{
    public function __construct(
        public string $name,
        public string $accountNumber,
        public string $bankCode,
        public string $type = 'nuban',
        public string $currency = 'NGN',
        public ?string $description = null,
    ) {}

    public function toRequestBody(): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'account_number' => $this->accountNumber,
            'bank_code' => $this->bankCode,
            'currency' => $this->currency,
            'description' => $this->description,
        ];
    }
}
