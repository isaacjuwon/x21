<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final readonly class PurchaseExam
{
    public function __construct(
        public string $service, // waec, neco, nabteb
        public int $numberOfPins = 1,
        public ?string $reference = null,
    ) {}

    public function toRequestBody(): array
    {
        return [
            'service' => $this->service,
            'numberOfPins' => $this->numberOfPins,
            'ref' => $this->reference,
        ];
    }
}
