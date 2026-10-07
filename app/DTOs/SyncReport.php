<?php

declare(strict_types=1);

namespace App\DTOs;

final class SyncReport
{
    /**
     * @param  list<string>  $errors
     */
    public function __construct(
        public string $provider,
        public int $created = 0,
        public int $merged = 0,
        public int $updated = 0,
        public array $errors = [],
    ) {}

    public function total(): int
    {
        return $this->created + $this->merged + $this->updated;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'created' => $this->created,
            'merged' => $this->merged,
            'updated' => $this->updated,
            'total' => $this->total(),
            'errors' => $this->errors,
        ];
    }
}
