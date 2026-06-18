<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final class PurchaseCable
{
    public string $service;

    public string $smartcardNumber;

    public string $variationCode;

    public int $amount;

    public function __construct(
        string $service,
        string $smartcardNumber,
        string $variationCode,
        int $amount,
        public readonly ?string $reference = null,
    ) {
        $this->service = strtolower(trim($service));
        $this->smartcardNumber = trim($smartcardNumber);
        $this->variationCode = trim($variationCode);
        $this->amount = $amount;
    }

    public function toRequestBody(): array
    {
        return [
            'service' => $this->service,
            'accountno' => $this->smartcardNumber,
            'vcode' => $this->variationCode,
            'amount' => $this->amount,
            'ref' => $this->reference,
        ];
    }
}
