<?php

declare(strict_types=1);

namespace App\Integrations\Dojah\Entities;

final class BvnMatchRequest
{
    public string $bvn {
        set(string $value) => trim($value);
    }

    public ?string $firstName {
        set(?string $value) => $value ? trim($value) : null;
    }

    public ?string $lastName {
        set(?string $value) => $value ? trim($value) : null;
    }

    public ?string $dob {
        set(?string $value) => $value ? trim($value) : null;
    }

    public function __construct(
        string $bvn,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $dob = null,
    ) {
        $this->bvn = $bvn;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->dob = $dob;
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
