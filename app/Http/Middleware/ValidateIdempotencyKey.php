<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pre-validates the client-provided idempotency key (header or input) before
 * the package middleware touches it. This rejects obviously bad values with
 * a clear 422 so callers see a validation-style error rather than a generic
 * 500 or an unintended passthrough when `required` is disabled.
 *
 * Place this BEFORE the Idempotent middleware in any route stack that serves
 * third-party callers you do not trust to send well-formed keys.
 *
 * Does nothing for GET/HEAD/OPTIONS (the package middleware also skips them).
 */
final class ValidateIdempotencyKey
{
    private const int MIN_KEY_LENGTH = 8;

    private const int MAX_KEY_LENGTH = 255;

    private const string KEY_PATTERN = '/^[A-Za-z0-9._:\-]+$/D';

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)) {
            return $next($request);
        }

        $headerName = config('idempotency.header', 'Idempotency-Key');
        $inputName = config('idempotency.input', '_idempotency_key');

        $headerValue = $request->headers->get($headerName);
        $inputValue = $request->input($inputName);

        $source = null;
        $rawKey = null;

        if (is_string($headerValue) && $headerValue !== '') {
            $source = 'header';
            $rawKey = $headerValue;
        } elseif (is_string($inputValue) && $inputValue !== '') {
            $source = 'input';
            $rawKey = $inputValue;
        }

        if ($rawKey === null) {
            return $next($request);
        }

        $validationError = $this->validateKey($rawKey);

        if ($validationError !== null) {
            Log::notice('Idempotency key validation failed', [
                'source' => $source,
                'error' => $validationError,
                'key_length' => strlen($rawKey),
                'path' => $request->path(),
                'method' => $request->method(),
                'ip' => $request->ip(),
            ]);

            $payload = [
                'code' => 'IDEMPOTENCY_KEY_INVALID',
                'message' => $validationError,
            ];

            if ($request->isJson() || $request->is('api/*') || $request->expectsJson()) {
                return new JsonResponse($payload, Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            abort(Response::HTTP_UNPROCESSABLE_ENTITY, $validationError);
        }

        return $next($request);
    }

    private function validateKey(string $key): ?string
    {
        $length = strlen($key);

        if ($length < self::MIN_KEY_LENGTH) {
            return sprintf(
                'Idempotency key is too short. Must be at least %d characters, got %d.',
                self::MIN_KEY_LENGTH,
                $length,
            );
        }

        if ($length > self::MAX_KEY_LENGTH) {
            return sprintf(
                'Idempotency key is too long. Must be at most %d characters, got %d.',
                self::MAX_KEY_LENGTH,
                $length,
            );
        }

        if (preg_match(self::KEY_PATTERN, $key) !== 1) {
            return 'Idempotency key contains invalid characters. Allowed: letters, digits, dot, underscore, colon, hyphen.';
        }

        return null;
    }
}
