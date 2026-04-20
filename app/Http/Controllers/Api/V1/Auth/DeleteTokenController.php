<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Support\SecurityAudit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class DeleteTokenController
{
    public function __invoke(Request $request, int $token_id): Response|JsonResponse
    {
        $user = $request->user();

        $token = $user->tokens()->whereKey($token_id)->first();

        if (! $token) {
            SecurityAudit::log('auth.tokens.revoke_failed', [
                'user_id' => (string) $user->getKey(),
                'token_id' => (string) $token_id,
            ]);

            return response()->json(['message' => 'Token not found.'], 404);
        }

        SecurityAudit::log('auth.tokens.revoked', [
            'user_id' => (string) $user->getKey(),
            'token_id' => (string) $token->getKey(),
        ]);

        $token->delete();

        return response()->noContent();
    }
}
