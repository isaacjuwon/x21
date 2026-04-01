<?php

namespace App\Http\Controllers\Api\V1\Auth;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Authentication', 'Obtain and revoke Sanctum tokens')]
#[Unauthenticated]
class ResetPasswordController
{
    #[BodyParam('token', 'string', description: 'The password reset token from the email', required: true, example: 'abc123token')]
    #[BodyParam('email', 'string', description: 'The user email address', required: true, example: 'user@example.com')]
    #[BodyParam('password', 'string', description: 'New password', required: true, example: 'newpassword')]
    #[BodyParam('password_confirmation', 'string', description: 'New password confirmation', required: true, example: 'newpassword')]
    #[Response(['message' => 'Your password has been reset.'], status: 200)]
    #[Response(['message' => 'This password reset token is invalid.'], status: 422)]
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __($status),
            ], 422);
        }

        return response()->json([
            'message' => __($status),
        ]);
    }
}
