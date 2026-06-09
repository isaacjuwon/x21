<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final class PurchaseElectricity
{
    public string $service;

    public string $meterNumber;

    public int $amount;

    public function __construct(
        string $service,
        string $meterNumber,
        int $amount,
        public readonly ?string $reference = null,
        public readonly ?string $phone = null,
    ) {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Electricity amount must be greater than zero.');
        }

        $this->service = strtolower(trim($service));
        $this->meterNumber = trim($meterNumber);
        $this->amount = $amount;
    }

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
