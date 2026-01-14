<?php

declare(strict_types=1);

namespace App\Integrations\Dojah\Entities;

final readonly class VerificationRequest
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $idType,
        public string $idNumber,
        public ?string $dob = null,
        public ?string $phone = null,
        public ?string $email = null,
    ) {}

    public function toRequestBody(): array
    {
        return array_filter([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'id_type' => $this->idType,
            'id_number' => $this->idNumber,
            'dob' => $this->dob,
            'phone' => $this->phone,
            'email' => $this->email,
        ], fn ($value) => $value !== null);
    }
}
