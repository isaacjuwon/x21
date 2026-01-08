<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final readonly class PurchaseCable
{
    public function __construct(
        public string $service, // dstv, gotv, startimes
        public string $smartcardNumber,
        public string $variationCode, // Plan code
        public ?string $reference = null,
        public ?string $phone = null,
    ) {}

    public function toRequestBody(): array
    {
        return [
            'service' => $this->service,
            'smartcard_number' => $this->smartcardNumber,
            'variation_code' => $this->variationCode,
            'ref' => $this->reference,
            'phone' => $this->phone,
        ];
    }
}
