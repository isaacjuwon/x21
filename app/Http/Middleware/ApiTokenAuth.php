<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ApiTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // bearerToken() can return null if the server strips Authorization header
        // Fall back to reading it directly from the header
        $token = $request->bearerToken()
            ?? $this->extractTokenFromHeader($request);

        if (! $token) {
            return new JsonResponse(['message' => 'Unauthenticated.'], 401);
        }

        $user = User::where('api_token', hash('sha256', $token))->first();

        if (! $user) {
            return new JsonResponse(['message' => 'Unauthenticated.'], 401);
        }

        auth()->setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }

    private function extractTokenFromHeader(Request $request): ?string
    {
        // Try all possible header variations
        $header = $request->header('Authorization')
            ?? $request->header('authorization')
            ?? $request->server('HTTP_AUTHORIZATION')
            ?? $request->server('REDIRECT_HTTP_AUTHORIZATION');

        if (! $header) {
            return null;
        }

        if (str_starts_with(strtolower($header), 'bearer ')) {
            return trim(substr($header, 7));
        }

        return null;
    }
}
