<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final class PurchaseElectricity
{
    public string $service {
        set (string $value) {
            $this->service = strtolower(trim($value));
        }
    }

    public string $meterNumber {
        set (string $value) {
            $this->meterNumber = trim($value);
        }
    }

    public int $amount {
        set (int $value) {
            if ($value <= 0) {
                throw new \InvalidArgumentException('Electricity amount must be greater than zero.');
            }
            $this->amount = $value;
        }
    }

    public function __construct(
        string $service,
        string $meterNumber,
        int $amount,
        public readonly ?string $reference = null,
        public readonly ?string $phone = null,
    ) {
        $this->service = $service;
        $this->meterNumber = $meterNumber;
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
