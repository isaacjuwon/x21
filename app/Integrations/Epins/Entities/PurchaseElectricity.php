<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final class PurchaseElectricity
{
    public string $service;

    public string $meterNumber;

    public string $meterType;

    public int $amount;

    public function __construct(
        string $service,
        string $meterNumber,
        string $meterType,
        int $amount,
        public readonly ?string $reference = null,
    ) {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Electricity amount must be greater than zero.');
        }

        $this->service = strtolower(trim($service));
        $this->meterNumber = trim($meterNumber);
        $this->meterType = trim($meterType);
        $this->amount = $amount;
    }

    public function toRequestBody(): array
    {
        return [
            'service' => $this->service,
            'accountno' => $this->meterNumber,
            'vcode' => $this->meterType,
            'amount' => $this->amount,
            'ref' => $this->reference,
        ];
    }
}
