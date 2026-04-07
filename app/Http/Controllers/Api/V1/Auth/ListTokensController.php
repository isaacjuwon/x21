<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Support\SecurityAudit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

final class ListTokensController
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentTokenId = $user->currentAccessToken()?->getKey();

        $tokens = $user->tokens()->latest('id')->get()->map(fn (PersonalAccessToken $token) => [
            'id' => $token->getKey(),
            'name' => $token->name,
            'abilities' => $token->abilities,
            'last_used_at' => $token->last_used_at?->toAtomString(),
            'expires_at' => $token->expires_at?->toAtomString(),
            'created_at' => $token->created_at?->toAtomString(),
            'is_current' => $currentTokenId !== null && (string) $currentTokenId === (string) $token->getKey(),
        ]);

        SecurityAudit::log('auth.tokens.listed', [
            'user_id' => (string) $user->getKey(),
            'count' => $tokens->count(),
        ]);

        return response()->json([
            'data' => $tokens,
            'meta' => ['count' => $tokens->count()],
        ]);
    }
}
