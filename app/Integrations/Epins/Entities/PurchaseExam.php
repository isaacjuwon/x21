<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final class PurchaseExam
{
    public string $service;

    public int $numberOfPins;

    public function __construct(
        string $service,
        int $numberOfPins = 1,
        public readonly ?string $reference = null,
    ) {
        if ($numberOfPins < 1) {
            throw new \InvalidArgumentException('Number of pins must be at least 1.');
        }

        $this->service = strtolower(trim($service));
        $this->numberOfPins = $numberOfPins;
    }

    public function toRequestBody(): array
    {
        return [
            'service' => $this->service,
            'numberOfPins' => $this->numberOfPins,
            'ref' => $this->reference,
        ];
    }
}
