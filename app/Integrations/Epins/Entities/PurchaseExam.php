<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final class PurchaseExam
{
    public string $service {
        set (string $value) {
            $this->service = strtolower(trim($value));
        }
    }

    public int $numberOfPins {
        set (int $value) {
            if ($value < 1) {
                throw new \InvalidArgumentException('Number of pins must be at least 1.');
            }
            $this->numberOfPins = $value;
        }
    }

    public function __construct(
        string $service,
        int $numberOfPins = 1,
        public readonly ?string $reference = null,
    ) {
        $this->service = $service;
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
