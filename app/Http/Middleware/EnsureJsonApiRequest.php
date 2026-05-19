<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureJsonApiRequest
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Restore Authorization header if Apache stripped it
        if (! $request->headers->has('Authorization')) {
            if ($request->server->has('HTTP_AUTHORIZATION')) {
                $request->headers->set('Authorization', $request->server->get('HTTP_AUTHORIZATION'));
            } elseif ($request->server->has('REDIRECT_HTTP_AUTHORIZATION')) {
                $request->headers->set('Authorization', $request->server->get('REDIRECT_HTTP_AUTHORIZATION'));
            }
        }

        // Force API exception rendering down the JSON path, even when clients omit Accept.
        $request->headers->set('Accept', 'application/json');

        if ($this->hasRequestPayload($request) && ! $request->isJson()) {
            return new JsonResponse([
                'message' => __('api.errors.unsupported_media_type'),
            ], Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        }

        $response = $next($request);

        // Baseline response hardening headers for API traffic.
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'no-referrer');

        return $response;
    }

    private function hasRequestPayload(Request $request): bool
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return false;
        }

        return $request->getContent() !== '' || $request->request->count() > 0;
    }
}
