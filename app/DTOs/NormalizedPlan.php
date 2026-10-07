<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\Plans\ServiceType;

final readonly class NormalizedPlan
{
    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function __construct(
        public string $provider,
        public string $providerCode,
        public string $brandSlug,
        public ServiceType $serviceType,
        public string $name,
        public ?string $type = null,
        public ?string $duration = null,
        public float $costPrice = 0.0,
        public ?string $canonicalKey = null,
        public ?array $meta = null,
    ) {}

    /**
     * Resolve or compute a canonical key if not explicitly provided.
     */
    public function resolvedCanonicalKey(): string
    {
        if ($this->canonicalKey !== null && $this->canonicalKey !== '') {
            return $this->canonicalKey;
        }

        $parts = [
            $this->brandSlug,
            $this->serviceType->value,
            $this->type ?? 'standard',
            $this->duration ?? 'standard',
            $this->name,
        ];

        $cleaned = array_map(function (string $part): string {
            return preg_replace('/[^a-z0-9]+/i', '_', strtolower(trim($part))) ?? '';
        }, $parts);

        return implode('_', array_filter($cleaned));
    }
}
