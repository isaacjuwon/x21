<?php

declare(strict_types=1);

namespace App\Http\Payloads\Share;

final readonly class SellSharePayload
{
    public function __construct(
        public int $quantity,
    ) {}
}
