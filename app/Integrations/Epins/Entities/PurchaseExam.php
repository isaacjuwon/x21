<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final class PurchaseExam
{
    public string $service;

    public string $variationCode;

    public int $amount;

    public int $numberOfPins;

    public function __construct(
        string $service,
        string $variationCode,
        int $amount,
        int $numberOfPins = 1,
        public readonly ?string $reference = null,
    ) {
        if ($numberOfPins < 1) {
            throw new \InvalidArgumentException('Number of pins must be at least 1.');
        }

        $this->service = strtolower(trim($service));
        $this->variationCode = trim($variationCode);
        $this->amount = $amount;
        $this->numberOfPins = $numberOfPins;
    }

    public function toRequestBody(): array
    {
        return [
            'service' => $this->service,
            'vcode' => $this->variationCode,
            'amount' => $this->amount,
            'quantity' => $this->numberOfPins,
            'ref' => $this->reference,
        ];
    }
}
