<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final readonly class ValidationResponse
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

    public function isValid(): bool
    {
        return $this->code === 119;
    }
}
