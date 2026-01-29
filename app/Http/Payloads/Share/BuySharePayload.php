<?php

declare(strict_types=1);

namespace App\Http\Payloads\Share;

final readonly class BuySharePayload
{
    public function __construct(
        public int $quantity,
    ) {}
}
