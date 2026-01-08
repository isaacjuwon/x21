<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final readonly class PurchaseAirtime
{
    public function __construct(
        public string $network, // 01=MTN, 02=Glo, 03=9Mobile, 04=Airtel
        public int $amount,
        public string $mobileNumber,
        public string $portedNumber = 'false',
        public ?string $reference = null,
    ) {}

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
