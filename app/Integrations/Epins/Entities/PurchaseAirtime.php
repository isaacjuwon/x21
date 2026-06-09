<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final class PurchaseAirtime
{
    public string $network;

    public string $mobileNumber;

    public int $amount;

    public function __construct(
        string $network,
        int $amount,
        string $mobileNumber,
        public readonly string $portedNumber = 'false',
        public readonly ?string $reference = null,
    ) {
        if (blank($network)) {
            throw new \InvalidArgumentException('Network code cannot be blank.');
        }
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Airtime amount must be greater than zero.');
        }

        $this->network = strtolower(trim($network));
        $this->mobileNumber = preg_replace('/\D/', '', $mobileNumber);
        $this->amount = $amount;
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
