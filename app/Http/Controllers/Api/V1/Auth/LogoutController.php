<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Support\SecurityAudit;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class LogoutController
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        if ($user) {
            SecurityAudit::log('auth.logout.succeeded', ['user_id' => (string) $user->getKey()]);
            $user->forceFill(['api_token' => null])->save();
        }

        return response()->noContent();
    }
}
