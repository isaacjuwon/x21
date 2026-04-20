<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Support\SecurityAudit;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response as ScribeResponse;

#[Group('Authentication', 'Register, login, and manage tokens')]
#[Authenticated]
final class LogoutController
{
    #[ScribeResponse(status: 204, description: 'Logged out successfully')]
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        if ($user) {
            SecurityAudit::log('auth.logout.succeeded', ['user_id' => (string) $user->getKey()]);
            $user->currentAccessToken()->delete();
        }

        return response()->noContent();
    }
}
