<?php

namespace App\Http\Controllers\Api\V1\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Authentication', 'Obtain and revoke Sanctum tokens')]
#[Unauthenticated]
class ForgotPasswordController
{
    #[BodyParam('email', 'string', description: 'The email address to send the reset link to', required: true, example: 'user@example.com')]
    #[Response(['message' => 'We have emailed your password reset link.'], status: 200)]
    #[Response(['message' => 'We can\'t find a user with that email address.'], status: 422)]
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => __($status),
            ], 422);
        }

        return response()->json([
            'message' => __($status),
        ]);
    }
}
