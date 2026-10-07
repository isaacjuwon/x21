<?php

declare(strict_types=1);

namespace App\Integrations\Contracts;

use App\DTOs\NormalizedPlan;

interface PlanNormalizer
{
    /**
     * Normalize upstream raw plan/variation payload into standardized NormalizedPlan DTOs.
     *
     * @param  array<string, mixed>  $rawPayload
     * @param  array<string, mixed>  $context  e.g. ['service_type' => ServiceType::Data, 'brand' => 'mtn']
     * @return list<NormalizedPlan>
     */
    public function normalize(array $rawPayload, array $context = []): array;
}
