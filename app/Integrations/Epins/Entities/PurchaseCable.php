<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final class PurchaseCable
{
    public string $service;

    public string $smartcardNumber;

    public string $variationCode;

    public function __construct(
        string $service,
        string $smartcardNumber,
        string $variationCode,
        public readonly ?string $reference = null,
        public readonly ?string $phone = null,
    ) {
        $this->service = strtolower(trim($service));
        $this->smartcardNumber = trim($smartcardNumber);
        $this->variationCode = trim($variationCode);
    }

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
