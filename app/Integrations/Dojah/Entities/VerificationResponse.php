<?php

declare(strict_types=1);

namespace App\Integrations\Dojah\Entities;

final readonly class VerificationResponse
{
    public function __construct(
        public bool $success,
        public ?array $data = null,
        public ?string $message = null,
    ) {}

    public static function fromResponse(array $response): self
    {
        return new self(
            success: $response['status'] ?? false,
            data: $response['data'] ?? null,
            message: $response['message'] ?? null,
        );
    }
}
