<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final class PurchaseCable
{
    public string $service {
        set (string $value) {
            $this->service = strtolower(trim($value));
        }
    }

    public string $smartcardNumber {
        set (string $value) {
            $this->smartcardNumber = trim($value);
        }
    }

    public string $variationCode {
        set (string $value) {
            $this->variationCode = trim($value);
        }
    }

    public function __construct(
        string $service,
        string $smartcardNumber,
        string $variationCode,
        public readonly ?string $reference = null,
        public readonly ?string $phone = null,
    ) {
        $this->service = $service;
        $this->smartcardNumber = $smartcardNumber;
        $this->variationCode = $variationCode;
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
