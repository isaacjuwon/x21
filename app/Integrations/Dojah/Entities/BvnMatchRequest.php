<?php

declare(strict_types=1);

namespace App\Integrations\Dojah\Entities;

final readonly class BvnMatchRequest
{
    public function __construct(
        public string $bvn,
        public string $firstName,
        public string $lastName,
        public ?string $dob = null,
    ) {}

    public function toQuery(): array
    {
        return array_filter([
            'bvn' => $this->bvn,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'dob' => $this->dob,
        ], fn ($value) => $value !== null);
    }
}
