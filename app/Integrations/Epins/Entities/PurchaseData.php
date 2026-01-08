<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final readonly class PurchaseData
{
    public function __construct(
        public string $network,
        public string $mobileNumber,
        public string $dataCode, // Variation code
        public ?string $reference = null,
    ) {}

    public function toRequestBody(): array
    {
        return [
            'network' => $this->network,
            'mobile_number' => $this->mobileNumber,
            'data_code' => $this->dataCode,
            'ref' => $this->reference,
        ];
    }
}
