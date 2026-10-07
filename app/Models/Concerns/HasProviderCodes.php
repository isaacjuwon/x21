<?php

namespace App\Models\Concerns;

use App\Models\PlanProviderCode;
use App\Settings\IntegrationSettings;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasProviderCodes
{
    public function providerCodes(): MorphMany
    {
        return $this->morphMany(PlanProviderCode::class, 'planable');
    }

    public function apiCodeFor(string $provider): ?string
    {
        return $this->providerCodes->firstWhere('provider', $provider)?->code;
    }

    /**
     * @return list<string>
     */
    protected function failoverProviderChain(): array
    {
        try {
            $settings = app(IntegrationSettings::class);
            if (! empty($settings->vtu_failover_providers)) {
                return (array) $settings->vtu_failover_providers;
            }
        } catch (\Throwable) {
            // Fall back to config if settings table/class unavailable
        }

        return (array) config('api.providers.failover.providers', ['vtugate']);
    }

    public function resolveApiCode(?string $preferredProvider = null): ?string
    {
        $chain = $this->failoverProviderChain();

        $candidates = [];
        if ($preferredProvider !== null) {
            $candidates[] = $preferredProvider;
        }
        foreach ($chain as $provider) {
            if (! in_array($provider, $candidates, true)) {
                $candidates[] = $provider;
            }
        }

        foreach ($candidates as $provider) {
            if ($code = $this->apiCodeFor($provider)) {
                return $code;
            }
        }

        return null;
    }

    public function getApiCodeAttribute(): ?string
    {
        $head = $this->failoverProviderChain()[0] ?? 'vtugate';

        return $this->resolveApiCode($head);
    }
}
