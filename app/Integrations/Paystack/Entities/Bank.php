<?php

declare(strict_types=1);

namespace App\Integrations\Paystack\Entities;

final readonly class Bank
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $code,
        public string $longcode,
        public ?string $gateway = null,
        public bool $payWithBank = false,
        public bool $active = true,
        public ?string $country = null,
        public ?string $currency = null,
        public ?string $type = null,
        public int $id = 0,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            slug: $data['slug'],
            code: $data['code'],
            longcode: $data['longcode'] ?? '',
            gateway: $data['gateway'] ?? null,
            payWithBank: $data['pay_with_bank'] ?? false,
            active: $data['active'] ?? true,
            country: $data['country'] ?? null,
            currency: $data['currency'] ?? null,
            type: $data['type'] ?? null,
            id: $data['id'] ?? 0,
            createdAt: $data['createdAt'] ?? null,
            updatedAt: $data['updatedAt'] ?? null,
        );
    }
}
