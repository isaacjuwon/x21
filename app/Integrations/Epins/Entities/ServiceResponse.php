<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final readonly class ServiceResponse
{
    public function __construct(
        public int $code,
        public array $description,
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            code: (int) $data['code'],
            description: is_array($data['description']) ? $data['description'] : ['message' => $data['description']],
        );
    }

    public function isSuccessful(): bool
    {
        return $this->code === 101;
    }
}
