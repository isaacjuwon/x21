<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final readonly class ValidateMeter
{
    public function __construct(
        public string $service, // ikeja-electric, etc.
        public string $meterNumber,
        public string $meterType, // prepaid, postpaid
    ) {}

    public function toRequestBody(): array
    {
        return [
            'service' => $this->service,
            'meter_number' => $this->meterNumber,
            'type' => $this->meterType,
        ];
    }
}
