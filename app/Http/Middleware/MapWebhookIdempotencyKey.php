<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Promotes a provider's natural dedupe identifier into the field that the
 * Idempotent middleware reads, so webhook deliveries are deduplicated
 * transparently without requiring providers to send an Idempotency-Key header.
 *
 * Place this BEFORE Idempotent in the route middleware stack.
 */
final class MapWebhookIdempotencyKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $provider = (string) $request->route('provider');
        $payload = (array) $request->json()->all();

        $key = match ($provider) {
            'paystack' => sprintf(
                '%s:%s',
                $payload['event'] ?? 'unknown',
                $payload['data']['reference'] ?? $payload['data']['id'] ?? '',
            ),
            'epins' => (string) ($payload['reference'] ?? $payload['id'] ?? ''),
            default => null,
        };

        if ($key !== null && $key !== '') {
            $request->merge([config('idempotency.input') => $key]);
        }

        return $next($request);
    }
}
