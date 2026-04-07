<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Payloads\V1\Auth\DeleteTokenPayload;
use App\Http\Requests\Auth\DeleteTokenRequest;
use App\Support\SecurityAudit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class DeleteTokenController
{
    public function __invoke(DeleteTokenRequest $request): Response|JsonResponse
    {
        $user = $request->user();
        $payload = DeleteTokenPayload::fromRequest($request);

        $token = $user->tokens()->whereKey($payload->tokenId)->first();

        if (! $token) {
            SecurityAudit::log('auth.tokens.revoke_failed', [
                'user_id' => (string) $user->getKey(),
                'token_id' => (string) $payload->tokenId,
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
