<?php

declare(strict_types=1);

namespace App\Http\Entities;

final class PurchaseCable
{
    public string $service;

    public string $smartcardNumber;

    public string $apiCode;

    public int $amount;

    public function __construct(
        string $service,
        string $smartcardNumber,
        string $apiCode,
        int $amount,
        public readonly ?string $reference = null,
        public readonly ?int $planId = null,
        public readonly ?string $planType = null,
    ) {
        $this->service = strtolower(trim($service));
        $this->smartcardNumber = trim($smartcardNumber);
        $this->apiCode = trim($apiCode);
        $this->amount = $amount;
    }

    public function toRequestBody(): array
    {
        return [
            'service' => $this->service,
            'accountno' => $this->smartcardNumber,
            'vcode' => $this->apiCode,
            'amount' => $this->amount,
            'ref' => $this->reference,
        ];
    }
}
