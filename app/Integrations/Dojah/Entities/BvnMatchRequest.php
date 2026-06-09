<?php

declare(strict_types=1);

namespace App\Integrations\Dojah\Entities;

final readonly class BvnMatchRequest
{
    public string $bvn;

    public ?string $firstName;

    public ?string $lastName;

    public ?string $dob;

    public function __construct(
        string $bvn,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $dob = null,
    ) {
        $this->bvn = trim($bvn);
        $this->firstName = $firstName ? trim($firstName) : null;
        $this->lastName = $lastName ? trim($lastName) : null;
        $this->dob = $dob ? trim($dob) : null;
    }

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
