<?php

namespace App\Http\Payloads\V1\Shares;

final readonly class BuySharePayload
{
    public function __construct(
        public int $quantity,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(quantity: (int) $data['quantity']);
    }
}
