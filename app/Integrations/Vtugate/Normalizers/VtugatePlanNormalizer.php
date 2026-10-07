<?php

declare(strict_types=1);

namespace App\Integrations\Vtugate\Normalizers;

use App\DTOs\NormalizedPlan;
use App\Enums\Plans\ServiceType;
use App\Integrations\Contracts\PlanNormalizer;
use App\Services\Vtu\PlanCanonicalizer;

final readonly class VtugatePlanNormalizer implements PlanNormalizer
{
    public function __construct(
        private PlanCanonicalizer $canonicalizer = new PlanCanonicalizer,
    ) {}

    /**
     * @param  array<string, mixed>  $rawPayload
     * @param  array<string, mixed>  $context
     * @return list<NormalizedPlan>
     */
    public function normalize(array $rawPayload, array $context = []): array
    {
        $serviceType = $context['service_type'] ?? null;

        if ($serviceType === null) {
            $serviceType = $this->inferServiceType($rawPayload, $context);
        }

        return match ($serviceType) {
            ServiceType::Data => $this->normalizeDataPlans($rawPayload, $context),
            ServiceType::Education, ServiceType::Exam => $this->normalizeEducationPlans($rawPayload, $context),
            ServiceType::Cable => $this->normalizeCablePlans($rawPayload, $context),
            default => $this->normalizeDataPlans($rawPayload, $context),
        };
    }

    /**
     * Normalize VtuGate data plans from /api/v1/fetchdataplans response.
     *
     * @param  array<string, mixed>  $rawPayload
     * @param  array<string, mixed>  $context
     * @return list<NormalizedPlan>
     */
    private function normalizeDataPlans(array $rawPayload, array $context): array
    {
        $rows = $rawPayload['data']['data_plans']
            ?? $rawPayload['data_plans']
            ?? (isset($rawPayload['code']) ? [$rawPayload] : []);

        $defaultBrand = (string) ($context['brand'] ?? $context['network_name'] ?? '');
        $plans = [];

        foreach ($rows as $item) {
            if (! is_array($item) || empty($item['code'])) {
                continue;
            }

            $rawName = (string) ($item['name'] ?? 'Data Plan');
            $brandSlug = $this->canonicalizer->normalizeBrand($defaultBrand !== '' ? $defaultBrand : $rawName);
            $parsed = $this->canonicalizer->parseDataPlan(
                name: $rawName,
                rawType: isset($item['type']) ? (string) $item['type'] : null,
                rawDuration: isset($item['duration']) ? (string) $item['duration'] : null,
            );

            $canonicalKey = $this->canonicalizer->buildDataCanonicalKey(
                brandSlug: $brandSlug,
                volumeSlug: $parsed['volume'],
                typeSlug: $parsed['type'],
                durationSlug: $parsed['duration'],
            );

            $brandName = (string) ($context['brand_name'] ?? strtoupper($brandSlug));
            $unifiedName = $this->canonicalizer->formatUnifiedName(
                brandName: $brandName,
                serviceType: ServiceType::Data,
                name: $rawName,
                type: $parsed['type'],
                duration: $parsed['duration'],
                volume: $parsed['volume'],
            );

            $plans[] = new NormalizedPlan(
                provider: 'vtugate',
                providerCode: (string) $item['code'],
                brandSlug: $brandSlug,
                serviceType: ServiceType::Data,
                name: $unifiedName,
                type: $parsed['type'],
                duration: $parsed['duration'],
                costPrice: (float) ($item['price'] ?? 0),
                canonicalKey: $canonicalKey,
                meta: [
                    'raw_name' => $rawName,
                    'volume' => $parsed['volume'],
                    'volume_mb' => $parsed['volume_mb'],
                    'service_id' => $context['service_id'] ?? null,
                ],
            );
        }

        return $plans;
    }

    /**
     * Normalize VtuGate education response.
     *
     * @param  array<string, mixed>  $rawPayload
     * @param  array<string, mixed>  $context
     * @return list<NormalizedPlan>
     */
    private function normalizeEducationPlans(array $rawPayload, array $context): array
    {
        $data = $rawPayload['data'] ?? $rawPayload;
        if (! is_array($data) || ! isset($data['price'])) {
            return [];
        }

        $brandRaw = (string) ($context['brand'] ?? $data['name'] ?? 'waec');
        $brandSlug = $this->canonicalizer->normalizeBrand($brandRaw);
        $name = (string) ($data['name'] ?? strtoupper($brandSlug).' Result Checker');
        $canonicalKey = $this->canonicalizer->buildEducationCanonicalKey($brandSlug, $name);
        $code = (string) ($data['service_id'] ?? $context['service_id'] ?? $brandSlug);

        return [
            new NormalizedPlan(
                provider: 'vtugate',
                providerCode: $code,
                brandSlug: $brandSlug,
                serviceType: ServiceType::Education,
                name: $name,
                type: 'PIN',
                duration: null,
                costPrice: (float) $data['price'],
                canonicalKey: $canonicalKey,
                meta: $data,
            ),
        ];
    }

    /**
     * Normalize VtuGate cable response.
     *
     * @param  array<string, mixed>  $rawPayload
     * @param  array<string, mixed>  $context
     * @return list<NormalizedPlan>
     */
    private function normalizeCablePlans(array $rawPayload, array $context): array
    {
        $rows = $rawPayload['data']['plans'] ?? $rawPayload['plans'] ?? $rawPayload['data'] ?? [];
        if (! is_array($rows)) {
            return [];
        }

        $brandRaw = (string) ($context['brand'] ?? 'dstv');
        $brandSlug = $this->canonicalizer->normalizeBrand($brandRaw);
        $plans = [];

        foreach ($rows as $item) {
            if (! is_array($item) || empty($item['code'])) {
                continue;
            }

            $name = (string) ($item['name'] ?? $item['package'] ?? 'Cable Package');
            $canonicalKey = $this->canonicalizer->buildCableCanonicalKey($brandSlug, $name);

            $plans[] = new NormalizedPlan(
                provider: 'vtugate',
                providerCode: (string) $item['code'],
                brandSlug: $brandSlug,
                serviceType: ServiceType::Cable,
                name: $name,
                type: 'Bouquet',
                duration: '1 Month',
                costPrice: (float) ($item['price'] ?? 0),
                canonicalKey: $canonicalKey,
                meta: $item,
            );
        }

        return $plans;
    }

    /**
     * Infer the service type from payload and context keys.
     *
     * @param  array<string, mixed>  $rawPayload
     * @param  array<string, mixed>  $context
     */
    private function inferServiceType(array $rawPayload, array $context): ServiceType
    {
        if (isset($context['service_type']) && $context['service_type'] instanceof ServiceType) {
            return $context['service_type'];
        }

        if (isset($rawPayload['data']['data_plans']) || isset($rawPayload['data_plans'])) {
            return ServiceType::Data;
        }

        if (isset($context['category']) && str_contains(strtolower((string) $context['category']), 'cable')) {
            return ServiceType::Cable;
        }

        return ServiceType::Data;
    }
}
