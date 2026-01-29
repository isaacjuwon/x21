<?php

declare(strict_types=1);

namespace App\Http\Payloads\User;

final readonly class UpdateUserPayload
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phoneNumber = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phoneNumber,
        ], fn ($value) => $value !== null);
    }
}
