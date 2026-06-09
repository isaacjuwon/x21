<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final class PurchaseAirtime
{
    public string $network {
        set (string $value) {
            if (blank($value)) {
                throw new \InvalidArgumentException('Network code cannot be blank.');
            }
            $this->network = strtolower(trim($value));
        }
    }

    public string $mobileNumber {
        set (string $value) {
            $this->mobileNumber = preg_replace('/\D/', '', $value);
        }
    }

    public int $amount {
        set (int $value) {
            if ($value <= 0) {
                throw new \InvalidArgumentException('Airtime amount must be greater than zero.');
            }
            $this->amount = $value;
        }
    }

    public function __construct(
        string $network,
        int $amount,
        string $mobileNumber,
        public readonly string $portedNumber = 'false',
        public readonly ?string $reference = null,
    ) {
        $this->network = $network;
        $this->amount = $amount;
        $this->mobileNumber = $mobileNumber;
    }

    public function toRequestBody(): array
    {
        return [
            'network' => $this->network,
            'amount' => $this->amount,
            'mobile_number' => $this->mobileNumber,
            'Ported_number' => $this->portedNumber,
            'airtime_type' => 'VTU',
            'ref' => $this->reference,
        ];
    }
}
