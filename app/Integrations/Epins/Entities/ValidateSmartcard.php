<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final readonly class ValidateSmartcard
{
    public function __construct(
        public string $service, // dstv, gotv, startimes
        public string $smartcardNumber,
    ) {}

    public function toRequestBody(): array
    {
        return [
            'service' => $this->service,
            'smartcard_number' => $this->smartcardNumber,
        ];
    }
}
