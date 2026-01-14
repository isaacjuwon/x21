<?php

namespace App\Http\Middleware;

use App\Settings\GeneralSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var GeneralSettings $settings */
        $settings = app(GeneralSettings::class);

        if (! $settings->maintenance_mode) {
            return $next($request);
        }

        // Allow access to admin routes, login, and skipped paths
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }

        // Allow access for authenticated admins/managers
        if (auth()->check() && auth()->user()->hasAnyRole(['super-admin', 'admin', 'manager'])) {
            return $next($request);
        }

        abort(503);
    }

    protected function shouldPassThrough(Request $request): bool
    {
        $except = [
            'admin*',
            'login',
            'logout',
            'register', // Depends if registration should be open, but usually maintenance blocks it. Keeping for now as auth route.
            'two-factor-challenge',
            'livewire/*', // vital for some livewire interactions if on checking page? No, 503 should block.
            'up', // Health check
        ];

        foreach ($except as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }
}
