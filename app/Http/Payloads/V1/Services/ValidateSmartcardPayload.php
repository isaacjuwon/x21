<?php

namespace App\Http\Payloads\V1\Services;

final readonly class ValidateSmartcardPayload
{
    public function __construct(
        public int $brandId,
        public string $smartCardNumber,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            brandId: (int) $data['brand_id'],
            smartCardNumber: (string) $data['smart_card_number'],
        );
    }
}
