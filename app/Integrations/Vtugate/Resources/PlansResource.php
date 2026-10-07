<?php

declare(strict_types=1);

namespace App\Integrations\Vtugate\Resources;

use App\Enums\Http\Method;
use App\Integrations\Vtugate\Exceptions\VtugateException;
use App\Integrations\Vtugate\VtugateConnector;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

final readonly class PlansResource
{
    public function __construct(
        private VtugateConnector $connector,
    ) {}

    /**
     * Fetch all services across every type (airtime, data, tv, electricity, education, broadband).
     * POST /api/v1/fetchallservices
     */
    public function fetchAllServices(): array
    {
        try {
            $response = $this->connector->send(
                method: Method::Post,
                uri: '/api/v1/fetchallservices',
                options: [],
            );

            return $response->json() ?? [];
        } catch (Throwable $exception) {
            throw new VtugateException(
                message: 'Failed to fetch all VtuGate services: '.$exception->getMessage(),
                previous: $exception,
            );
        }
    }

    /**
     * Fetch services filtered by type (e.g. 'airtime', 'data', 'tv', 'electricity', 'education', 'sms').
     * POST /api/v1/fetchservices
     */
    public function fetchServices(string $serviceType): array
    {
        try {
            $response = $this->connector->send(
                method: Method::Post,
                uri: '/api/v1/fetchservices',
                options: ['service_type' => $serviceType],
            );

            return $response->json() ?? [];
        } catch (Throwable $exception) {
            throw new VtugateException(
                message: "Failed to fetch VtuGate services for type [{$serviceType}]: ".$exception->getMessage(),
                previous: $exception,
            );
        }
    }

    /**
     * Fetch data plans for a specific service ID.
     * POST /api/v1/fetchdataplans
     */
    public function fetchDataPlans(int|string $serviceId): array
    {
        try {
            $response = $this->connector->send(
                method: Method::Post,
                uri: '/api/v1/fetchdataplans',
                options: ['service_id' => $serviceId],
            );

            return $response->json() ?? [];
        } catch (Throwable $exception) {
            throw new VtugateException(
                message: "Failed to fetch VtuGate data plans for service [{$serviceId}]: ".$exception->getMessage(),
                previous: $exception,
            );
        }
    }

    /**
     * Fetch education type price for a specific service ID.
     * POST /api/v1/geteducationtypeprice
     */
    public function fetchEducationPrice(int|string $serviceId): array
    {
        try {
            $response = $this->connector->send(
                method: Method::Post,
                uri: '/api/v1/geteducationtypeprice',
                options: ['service_id' => $serviceId],
            );

            return $response->json() ?? [];
        } catch (Throwable $exception) {
            throw new VtugateException(
                message: "Failed to fetch VtuGate education price for service [{$serviceId}]: ".$exception->getMessage(),
                previous: $exception,
            );
        }
    }

    /**
     * Fetch data plans concurrently for multiple service IDs using Laravel Http::pool.
     *
     * @param  array<int|string>  $serviceIds
     * @return array<string, array>
     */
    public function fetchDataPlansPool(array $serviceIds): array
    {
        if (empty($serviceIds)) {
            return [];
        }

        $responses = Http::pool(function (Pool $pool) use ($serviceIds) {
            return array_map(
                fn ($serviceId) => $this->connector->pooledRequest($pool, (string) $serviceId)
                    ->post('/api/v1/fetchdataplans', ['service_id' => $serviceId]),
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
     * Fetch all data plans across all data services.
     * If $serviceIds is null, discovers all data services from VtuGate first, then batches concurrently.
     *
     * @param  array<int|string>|null  $serviceIds
     * @return array<string, array>
     */
    public function fetchAllDataPlans(?array $serviceIds = null): array
    {
        if ($serviceIds === null) {
            $servicesData = $this->fetchServices('data');
            $rows = $servicesData['data'] ?? [];
            $serviceIds = array_filter(array_column($rows, 'service_id'));
        }

        return $this->fetchDataPlansPool($serviceIds);
    }
}
