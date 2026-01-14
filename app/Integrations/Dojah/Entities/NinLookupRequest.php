<?php

declare(strict_types=1);

namespace App\Integrations\Dojah\Entities;

final readonly class NinLookupRequest
{
    public function __construct(
        public string $nin,
    ) {}

    public function toQuery(): array
    {
        return [
            'nin' => $this->nin,
        ];
    }
}
