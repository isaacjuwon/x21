<?php

namespace App\Managers\Concerns;

use Illuminate\Support\Facades\Cache;

trait InteractsWithFakeApi
{
    /**
     * Determine if payment API requests are being faked.
     */
    public function paymentsAreFaked(): bool
    {
        return Cache::get('api.faking.payments', false);
    }

    /**
     * Determine if VTU API requests are being faked.
     */
    public function vtuAreFaked(): bool
    {
        return Cache::get('api.faking.vtu', false);
    }

    /**
     * Determine if account API requests are being faked.
     */
    public function accountsAreFaked(): bool
    {
        return Cache::get('api.faking.accounts', false);
    }

    /**
     * Fake the payment API responses.
     */
    public function fakePayments(): void
    {
        Cache::put('api.faking.payments', true);
    }

    /**
     * Fake the VTU API responses.
     */
    public function fakeVtu(): void
    {
        Cache::put('api.faking.vtu', true);
    }

    /**
     * Fake the account API responses.
     */
    public function fakeAccounts(): void
    {
        Cache::put('api.faking.accounts', true);
    }

    /**
     * Record an API request.
     */
    public function recordRequest(string $type, string $method, string $url, array $payload = [], array $response = []): void
    {
        $requests = Cache::get("api.requests.{$type}", []);

        $requests[] = [
            'method' => $method,
            'url' => $url,
            'payload' => $payload,
            'response' => $response,
            'timestamp' => now()->toDateTimeString(),
        ];

        Cache::put("api.requests.{$type}", $requests);
    }

    /**
     * Get the recorded API requests.
     */
    public function recordedRequests(string $type): array
    {
        return Cache::get("api.requests.{$type}", []);
    }

    /**
     * Clear the recorded API requests.
     */
    public function clearRecordedRequests(string $type): void
    {
        Cache::forget("api.requests.{$type}");
    }
}
