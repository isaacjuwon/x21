<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Support\SecurityAudit;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Sanctum\PersonalAccessToken;

final class LogoutController
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        /** @var PersonalAccessToken|null $token */
        $token = $user?->currentAccessToken();

        if ($token) {
            SecurityAudit::log('auth.logout.succeeded', [
                'user_id' => (string) $user->getKey(),
                'token_id' => (string) $token->getKey(),
            ]);

            $token->delete();
        }

        return response()->noContent();
    }
}
