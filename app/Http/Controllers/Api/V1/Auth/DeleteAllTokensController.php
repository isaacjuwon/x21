<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Support\SecurityAudit;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class DeleteAllTokensController
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $count = $user->tokens()->count();
        $user->tokens()->delete();

        SecurityAudit::log('auth.tokens.revoked_all', [
            'user_id' => (string) $user->getKey(),
            'count' => $count,
        ]);

        return response()->noContent();
    }
}
