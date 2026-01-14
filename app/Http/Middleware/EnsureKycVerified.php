<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKycVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $redirectTo = 'kyc.index'): Response
    {
        if ($request->user() && !$request->user()->isVerified()) {
            return redirect()->route($redirectTo)->with('warning', 'Please complete KYC verification to access this feature.');
        }

        return $next($request);
    }
}
