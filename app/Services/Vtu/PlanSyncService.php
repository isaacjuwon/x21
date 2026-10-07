<?php

declare(strict_types=1);

namespace App\Services\Vtu;

use App\DTOs\NormalizedPlan;
use App\DTOs\SyncReport;
use App\Enums\Plans\ServiceType;
use App\Integrations\Vtpass\Normalizers\VtpassPlanNormalizer;
use App\Integrations\Vtpass\Resources\PlansResource as VtpassPlansResource;
use App\Integrations\Vtpass\VtpassConnector;
use App\Integrations\Vtugate\Normalizers\VtugatePlanNormalizer;
use App\Integrations\Vtugate\VtugateConnector;
use App\Models\Brand;
use App\Models\Plan;
use Illuminate\Support\Facades\Log;
use Throwable;

class PlanSyncService
{
    public function __construct(
        protected VtugateConnector $vtugateConnector,
        protected VtpassConnector $vtpassConnector,
        protected VtugatePlanNormalizer $vtugateNormalizer,
        protected VtpassPlanNormalizer $vtpassNormalizer,
        protected PricingService $pricingService,
        protected PlanCanonicalizer $canonicalizer,
    ) {}

    /**
     * Sync plans from all configured providers.
     *
     * @return array<string, SyncReport>
     */
    public function syncAll(?ServiceType $serviceType = null): array
    {
        return [
            'vtugate' => $this->syncProvider('vtugate', $serviceType),
            'vtpass' => $this->syncProvider('vtpass', $serviceType),
        ];
    }

    /**
     * Sync plans from a specific provider.
     */
    public function syncProvider(string $provider, ?ServiceType $serviceType = null): SyncReport
    {
        $providerLower = strtolower(trim($provider));

        return match ($providerLower) {
            'vtugate' => $this->syncVtugate($serviceType),
            'vtpass' => $this->syncVtpass($serviceType),
            default => throw new \InvalidArgumentException("Unsupported VTU plan provider [{$provider}]."),
        };
    }

    /**
     * Fetch and persist VtuGate plans.
     */
    public function syncVtugate(?ServiceType $serviceType = null): SyncReport
    {
        $report = new SyncReport(provider: 'vtugate');
        $plansResource = $this->vtugateConnector->plans();

        // 1. Data Plans
        if ($serviceType === null || $serviceType === ServiceType::Data) {
            try {
                $dataResponses = $plansResource->fetchAllDataPlans();
                foreach ($dataResponses as $serviceId => $raw) {
                    if (isset($raw['error'])) {
                        $report->errors[] = "VtuGate data [{$serviceId}]: ".($raw['message'] ?? 'Fetch error');

                        continue;
                    }

                    $normalizedList = $this->vtugateNormalizer->normalize($raw, [
                        'service_type' => ServiceType::Data,
                        'service_id' => $serviceId,
                    ]);

                    $this->processNormalizedList($normalizedList, $report);
                }
            } catch (Throwable $e) {
                Log::error('VtuGate data plan sync failed: '.$e->getMessage());
                $report->errors[] = 'VtuGate data sync: '.$e->getMessage();
            }
        }

        // 2. Education Plans
        if ($serviceType === null || $serviceType === ServiceType::Education) {
            try {
                $services = $plansResource->fetchServices('education');
                $rows = $services['data'] ?? [];

                foreach ($rows as $srv) {
                    $serviceId = $srv['service_id'] ?? null;
                    if (! $serviceId) {
                        continue;
                    }

                    $priceData = $plansResource->fetchEducationPrice($serviceId);
                    $normalizedList = $this->vtugateNormalizer->normalize($priceData, [
                        'service_type' => ServiceType::Education,
                        'service_id' => $serviceId,
                        'brand' => $srv['network_name'] ?? $srv['name'] ?? 'waec',
                    ]);

                    $this->processNormalizedList($normalizedList, $report);
                }
            } catch (Throwable $e) {
                Log::error('VtuGate education sync failed: '.$e->getMessage());
                $report->errors[] = 'VtuGate education sync: '.$e->getMessage();
            }
        }

        return $report;
    }

    /**
     * Fetch and persist VTPass plans.
     */
    public function syncVtpass(?ServiceType $serviceType = null): SyncReport
    {
        $report = new SyncReport(provider: 'vtpass');
        $plansResource = $this->vtpassConnector->plans();

        // 1. Data Plans
        if ($serviceType === null || $serviceType === ServiceType::Data) {
            try {
                $dataResponses = $plansResource->fetchAllDataPlans();
                foreach ($dataResponses as $serviceId => $raw) {
                    if (isset($raw['error'])) {
                        $report->errors[] = "VTPass data [{$serviceId}]: ".($raw['message'] ?? 'Fetch error');

                        continue;
                    }

                    $normalizedList = $this->vtpassNormalizer->normalize($raw, [
                        'service_type' => ServiceType::Data,
                        'service_id' => $serviceId,
                    ]);

                    $this->processNormalizedList($normalizedList, $report);
                }
            } catch (Throwable $e) {
                Log::error('VTPass data plan sync failed: '.$e->getMessage());
                $report->errors[] = 'VTPass data sync: '.$e->getMessage();
            }
        }

        // 2. Cable Plans
        if ($serviceType === null || $serviceType === ServiceType::Cable) {
            try {
                $cableServices = VtpassPlansResource::DEFAULT_CABLE_SERVICES;
                $cableResponses = $plansResource->fetchVariationsPool($cableServices);

                foreach ($cableResponses as $serviceId => $raw) {
                    if (isset($raw['error'])) {
                        $report->errors[] = "VTPass cable [{$serviceId}]: ".($raw['message'] ?? 'Fetch error');

                        continue;
                    }

                    $normalizedList = $this->vtpassNormalizer->normalize($raw, [
                        'service_type' => ServiceType::Cable,
                        'service_id' => $serviceId,
                    ]);

                    $this->processNormalizedList($normalizedList, $report);
                }
            } catch (Throwable $e) {
                Log::error('VTPass cable sync failed: '.$e->getMessage());
                $report->errors[] = 'VTPass cable sync: '.$e->getMessage();
            }
        }

        // 3. Education Plans
        if ($serviceType === null || $serviceType === ServiceType::Education) {
            try {
                $eduServices = VtpassPlansResource::DEFAULT_EDUCATION_SERVICES;
                $eduResponses = $plansResource->fetchVariationsPool($eduServices);

                foreach ($eduResponses as $serviceId => $raw) {
                    if (isset($raw['error'])) {
                        $report->errors[] = "VTPass education [{$serviceId}]: ".($raw['message'] ?? 'Fetch error');

                        continue;
                    }

                    $normalizedList = $this->vtpassNormalizer->normalize($raw, [
                        'service_type' => ServiceType::Education,
                        'service_id' => $serviceId,
                    ]);

                    $this->processNormalizedList($normalizedList, $report);
                }
            } catch (Throwable $e) {
                Log::error('VTPass education sync failed: '.$e->getMessage());
                $report->errors[] = 'VTPass education sync: '.$e->getMessage();
            }
        }

        return $report;
    }

    /**
     * Persist or merge a collection of NormalizedPlan instances.
     *
     * @param  list<NormalizedPlan>  $plans
     */
    public function syncNormalized(array $plans, string $providerName = 'custom'): SyncReport
    {
        $report = new SyncReport(provider: $providerName);
        $this->processNormalizedList($plans, $report);

        return $report;
    }

    /**
     * Process list of NormalizedPlan DTOs into the database.
     *
     * @param  list<NormalizedPlan>  $normalizedList
     */
    protected function processNormalizedList(array $normalizedList, SyncReport $report): void
    {
        foreach ($normalizedList as $normalized) {
            try {
                $result = $this->persistNormalizedPlan($normalized);

                match ($result['action']) {
                    'created' => $report->created++,
                    'merged' => $report->merged++,
                    'updated' => $report->updated++,
                };
            } catch (Throwable $e) {
                Log::error("Failed to persist normalized plan [{$normalized->name}]: ".$e->getMessage());
                $report->errors[] = "Plan [{$normalized->name}]: ".$e->getMessage();
            }
        }
    }

    /**
     * Persist a single NormalizedPlan into the unified Plan model.
     * If an existing plan matches the canonical key, attach/update the provider code and merge.
     *
     * @return array{action: 'created'|'merged'|'updated', plan: Plan}
     */
    public function persistNormalizedPlan(NormalizedPlan $normalized): array
    {
        $brandSlug = $this->canonicalizer->normalizeBrand($normalized->brandSlug);
        $brand = Brand::firstOrCreate(
            ['slug' => $brandSlug],
            [
                'name' => strtoupper($brandSlug),
                'api_code' => strtoupper($brandSlug),
                'status' => true,
            ],
        );

        $canonicalKey = $normalized->resolvedCanonicalKey();

        // 1. Search for existing Plan by canonical key in meta
        $existing = Plan::where('brand_id', $brand->id)
            ->where('service_type', $normalized->serviceType)
            ->where('meta->canonical_key', $canonicalKey)
            ->first();

        // 2. Fallback search by (service_type + type + duration + volume)
        if (! $existing && $normalized->serviceType === ServiceType::Data) {
            $normVol = $normalized->meta['volume'] ?? null;
            if ($normVol !== null) {
                $existing = Plan::where('brand_id', $brand->id)
                    ->where('service_type', ServiceType::Data)
                    ->where('type', $normalized->type)
                    ->where('duration', $normalized->duration)
                    ->get()
                    ->first(function (Plan $plan) use ($normVol): bool {
                        $vol = $plan->meta['volume'] ?? null;

                        return $vol !== null && strtolower((string) $vol) === strtolower((string) $normVol);
                    });
            }
        }

        if ($existing) {
            $hasProviderCode = $existing->hasProviderCode($normalized->provider);

            // Attach / update this provider's code
            $existing->setProviderCode($normalized->provider, $normalized->providerCode);

            // Update meta with canonical_key and volume if missing
            $meta = $existing->meta ?? [];
            if (! isset($meta['canonical_key'])) {
                $meta['canonical_key'] = $canonicalKey;
            }
            if (isset($normalized->meta['volume']) && ! isset($meta['volume'])) {
                $meta['volume'] = $normalized->meta['volume'];
            }
            $existing->meta = $meta;

            // If existing cost_price is empty, or if updating wholesale cost
            if ($existing->cost_price === null || (float) $existing->cost_price <= 0.0 || $normalized->provider === 'vtugate') {
                $existing->cost_price = $normalized->costPrice;
                $existing->price = $this->pricingService->calculate(
                    $existing,
                    customPrice: isset($meta['custom_price']) ? (float) $meta['custom_price'] : null
                );
            }

            $existing->save();

            return [
                'action' => $hasProviderCode ? 'updated' : 'merged',
                'plan' => $existing,
            ];
        }

        // 3. Create new Plan
        $cost = $normalized->costPrice;
        $price = $this->pricingService->calculate($cost);

        $plan = Plan::create([
            'brand_id' => $brand->id,
            'service_type' => $normalized->serviceType,
            'name' => $normalized->name,
            'type' => $normalized->type,
            'duration' => $normalized->duration,
            'price' => $price,
            'cost_price' => $cost,
            'status' => true,
            'meta' => array_merge($normalized->meta ?? [], [
                'canonical_key' => $canonicalKey,
            ]),
        ]);

        $plan->setProviderCode($normalized->provider, $normalized->providerCode);

        return [
            'action' => 'created',
            'plan' => $plan,
        ];
    }
}
