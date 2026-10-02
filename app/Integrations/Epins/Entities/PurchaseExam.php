<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final class PurchaseExam
{
    public string $service;

    public string $apiCode;

    public int $amount;

    public int $numberOfPins;

    public function __construct(
        string $service,
        string $apiCode,
        int $amount,
        int $numberOfPins = 1,
        public readonly ?string $reference = null,
        public readonly ?int $planId = null,
        public readonly ?string $planType = null,
    ) {
        if ($numberOfPins < 1) {
            throw new \InvalidArgumentException('Number of pins must be at least 1.');
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Exam PIN amount must be greater than zero.');
        }

        $this->service = strtolower(trim($service));
        $this->apiCode = trim($apiCode);
        $this->amount = $amount;
        $this->numberOfPins = $numberOfPins;
    }

    public function toRequestBody(): array
    {
        return [
            'service' => $this->service,
            'vcode' => $this->apiCode,
            'amount' => $this->amount,
            'quantity' => $this->numberOfPins,
            'ref' => $this->reference,
        ];
    }
}
