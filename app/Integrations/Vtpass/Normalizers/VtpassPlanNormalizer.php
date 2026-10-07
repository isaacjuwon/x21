<?php

declare(strict_types=1);

namespace App\Integrations\Vtpass\Normalizers;

use App\DTOs\NormalizedPlan;
use App\Enums\Plans\ServiceType;
use App\Integrations\Contracts\PlanNormalizer;
use App\Services\Vtu\PlanCanonicalizer;

final readonly class VtpassPlanNormalizer implements PlanNormalizer
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
        $content = $rawPayload['content'] ?? $rawPayload;
        $variations = $content['variations'] ?? $rawPayload['variations'] ?? [];

        if (! is_array($variations) || empty($variations)) {
            return [];
        }

        $serviceId = (string) ($content['serviceID'] ?? $context['service_id'] ?? $context['serviceID'] ?? '');
        $serviceType = $context['service_type'] ?? $this->inferServiceType($serviceId, $context);
        $brandSlug = $this->canonicalizer->normalizeBrand($serviceId !== '' ? $serviceId : ($context['brand'] ?? ''));

        return match ($serviceType) {
            ServiceType::Data => $this->normalizeDataVariations($variations, $brandSlug, $context),
            ServiceType::Cable => $this->normalizeCableVariations($variations, $brandSlug, $context),
            ServiceType::Education, ServiceType::Exam => $this->normalizeEducationVariations($variations, $brandSlug, $context),
            default => $this->normalizeDataVariations($variations, $brandSlug, $context),
        };
    }

    /**
     * @param  list<array<string, mixed>>  $variations
     * @param  array<string, mixed>  $context
     * @return list<NormalizedPlan>
     */
    private function normalizeDataVariations(array $variations, string $brandSlug, array $context): array
    {
        $plans = [];

        foreach ($variations as $item) {
            if (! is_array($item) || empty($item['variation_code'])) {
                continue;
            }

            $rawName = (string) ($item['name'] ?? 'Data Plan');
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
                provider: 'vtpass',
                providerCode: (string) $item['variation_code'],
                brandSlug: $brandSlug,
                serviceType: ServiceType::Data,
                name: $unifiedName,
                type: $parsed['type'],
                duration: $parsed['duration'],
                costPrice: (float) ($item['variation_amount'] ?? 0),
                canonicalKey: $canonicalKey,
                meta: [
                    'raw_name' => $rawName,
                    'volume' => $parsed['volume'],
                    'volume_mb' => $parsed['volume_mb'],
                    'fixed_price' => $item['fixedPrice'] ?? null,
                ],
            );
        }

        return $plans;
    }

    /**
     * @param  list<array<string, mixed>>  $variations
     * @param  array<string, mixed>  $context
     * @return list<NormalizedPlan>
     */
    private function normalizeCableVariations(array $variations, string $brandSlug, array $context): array
    {
        $plans = [];

        foreach ($variations as $item) {
            if (! is_array($item) || empty($item['variation_code'])) {
                continue;
            }

            $rawName = (string) ($item['name'] ?? 'Bouquet');
            $canonicalKey = $this->canonicalizer->buildCableCanonicalKey($brandSlug, $rawName);

            $plans[] = new NormalizedPlan(
                provider: 'vtpass',
                providerCode: (string) $item['variation_code'],
                brandSlug: $brandSlug,
                serviceType: ServiceType::Cable,
                name: $rawName,
                type: 'Bouquet',
                duration: '1 Month',
                costPrice: (float) ($item['variation_amount'] ?? 0),
                canonicalKey: $canonicalKey,
                meta: $item,
            );
        }

        return $plans;
    }

    /**
     * @param  list<array<string, mixed>>  $variations
     * @param  array<string, mixed>  $context
     * @return list<NormalizedPlan>
     */
    private function normalizeEducationVariations(array $variations, string $brandSlug, array $context): array
    {
        $plans = [];

        foreach ($variations as $item) {
            if (! is_array($item) || empty($item['variation_code'])) {
                continue;
            }

            $rawName = (string) ($item['name'] ?? 'Exam PIN');
            $canonicalKey = $this->canonicalizer->buildEducationCanonicalKey($brandSlug, $rawName);

            $plans[] = new NormalizedPlan(
                provider: 'vtpass',
                providerCode: (string) $item['variation_code'],
                brandSlug: $brandSlug,
                serviceType: ServiceType::Education,
                name: $rawName,
                type: 'PIN',
                duration: null,
                costPrice: (float) ($item['variation_amount'] ?? 0),
                canonicalKey: $canonicalKey,
                meta: $item,
            );
        }

        return $plans;
    }

    /**
     * Infer service type from serviceID and context.
     *
     * @param  array<string, mixed>  $context
     */
    private function inferServiceType(string $serviceId, array $context): ServiceType
    {
        if (isset($context['service_type']) && $context['service_type'] instanceof ServiceType) {
            return $context['service_type'];
        }

        $sid = strtolower($serviceId);

        if (str_ends_with($sid, '-data') || str_contains($sid, 'smile') || str_contains($sid, 'spectranet')) {
            return ServiceType::Data;
        }

        if (in_array($sid, ['dstv', 'gotv', 'startimes', 'showmax'], true)) {
            return ServiceType::Cable;
        }

        if (in_array($sid, ['waec', 'waec-registration', 'jamb'], true)) {
            return ServiceType::Education;
        }

        if (str_contains($sid, 'electric')) {
            return ServiceType::Electricity;
        }

        return ServiceType::Data;
    }
}
