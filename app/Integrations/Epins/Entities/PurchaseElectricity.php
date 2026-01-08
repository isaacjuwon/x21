<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final readonly class PurchaseElectricity
{
    public function __construct(
        public string $service,
        public string $meterNumber,
        public int $amount,
        public ?string $reference = null,
        public ?string $phone = null,
    ) {}

    public function toRequestBody(): array
    {
        return [
            'service' => $this->service,
            'meter_number' => $this->meterNumber,
            'amount' => $this->amount,
            'ref' => $this->reference,
            'phone' => $this->phone,
        ];
    }
}
