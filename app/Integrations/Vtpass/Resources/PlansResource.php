<?php

declare(strict_types=1);

namespace App\Integrations\Vtpass\Resources;

use App\Enums\Http\Method;
use App\Integrations\Vtpass\Exceptions\VtpassException;
use App\Integrations\Vtpass\VtpassConnector;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

final readonly class PlansResource
{
    /**
     * Default service IDs for data products.
     */
    public const DEFAULT_DATA_SERVICES = [
        'mtn-data',
        'airtel-data',
        'glo-data',
        'glo-sme-data',
        'etisalat-data',
        'smile-direct',
        'spectranet',
    ];

    /**
     * Default service IDs for cable TV products.
     */
    public const DEFAULT_CABLE_SERVICES = [
        'dstv',
        'gotv',
        'startimes',
        'showmax',
    ];

    /**
     * Default service IDs for education products.
     */
    public const DEFAULT_EDUCATION_SERVICES = [
        'waec',
        'waec-registration',
        'jamb',
    ];

    /**
     * Default service IDs for electricity products.
     */
    public const DEFAULT_ELECTRICITY_SERVICES = [
        'ikeja-electric',
        'eko-electric',
        'kano-electric',
        'portharcourt-electric',
        'jos-electric',
        'ibadan-electric',
        'kaduna-electric',
        'aedc-abuja',
        'eedc-enugu',
        'bedc-benin',
        'aba-electric',
        'yedc-yola',
    ];

    public function __construct(
        private VtpassConnector $connector,
    ) {}

    /**
     * Fetch all available service categories.
     * GET /service-categories
     */
    public function fetchCategories(): array
    {
        try {
            $response = $this->connector->send(
                method: Method::Get,
                uri: '/service-categories',
            );

            return $response->json() ?? [];
        } catch (Throwable $exception) {
            throw new VtpassException(
                message: 'Failed to fetch VTPass service categories: '.$exception->getMessage(),
                previous: $exception,
            );
        }
    }

    /**
     * Fetch available services, optionally filtered by identifier/category.
     * GET /services?identifier={category}
     */
    public function fetchServices(?string $category = null): array
    {
        try {
            $query = $category !== null ? ['identifier' => $category] : [];
            $response = $this->connector->send(
                method: Method::Get,
                uri: '/services',
                options: $query,
            );

            return $response->json() ?? [];
        } catch (Throwable $exception) {
            throw new VtpassException(
                message: 'Failed to fetch VTPass services: '.$exception->getMessage(),
                previous: $exception,
            );
        }
    }

    /**
     * Fetch variation codes for a specific service.
     * GET /service-variations?serviceID={serviceID}
     */
    public function fetchVariations(string $serviceId): array
    {
        try {
            $response = $this->connector->send(
                method: Method::Get,
                uri: '/service-variations',
                options: ['serviceID' => $serviceId],
            );

            return $response->json() ?? [];
        } catch (Throwable $exception) {
            throw new VtpassException(
                message: "Failed to fetch VTPass variations for [{$serviceId}]: ".$exception->getMessage(),
                previous: $exception,
            );
        }
    }

    /**
     * Fetch variations concurrently for multiple service IDs using Laravel Http::pool.
     *
     * @param  string[]  $serviceIds
     * @return array<string, array>
     */
    public function fetchVariationsPool(array $serviceIds): array
    {
        if (empty($serviceIds)) {
            return [];
        }

        $responses = Http::pool(function (Pool $pool) use ($serviceIds) {
            return array_map(
                fn (string $serviceId) => $this->connector->pooledRequest($pool, $serviceId)
                    ->get('/service-variations', ['serviceID' => $serviceId]),
                $serviceIds,
            );
        });

        $results = [];
        foreach ($responses as $key => $response) {
            if ($response instanceof Response && $response->successful()) {
                $results[$key] = $response->json() ?? [];
            } elseif ($response instanceof Response) {
                $results[$key] = [
                    'error' => true,
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ];
            } elseif ($response instanceof Throwable) {
                $results[$key] = [
                    'error' => true,
                    'message' => $response->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Fetch variations across all data networks concurrently.
     *
     * @param  string[]|null  $serviceIds
     * @return array<string, array>
     */
    public function fetchAllDataPlans(?array $serviceIds = null): array
    {
        $services = $serviceIds ?? self::DEFAULT_DATA_SERVICES;

        return $this->fetchVariationsPool($services);
    }
}
