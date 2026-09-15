<?php

namespace App\Http\Payloads\V1\Services;

final readonly class ValidateMeterPayload
{
    public function __construct(
        public int $brandId,
        public string $meterNumber,
        public string $meterType,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            brandId: (int) $data['brand_id'],
            meterNumber: (string) $data['meter_number'],
            meterType: (string) $data['meter_type'],
        );
    }
}
