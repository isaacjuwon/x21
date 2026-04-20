<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Payloads\V1\Auth\ResetPasswordPayload;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Support\SecurityAudit;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Authentication', 'Register, login, and manage tokens')]
final class ResetPasswordController
{
    #[Unauthenticated]
    #[Response(['message' => 'Your password has been reset.'], status: 200, description: 'Password reset successful')]
    #[Response(['message' => 'This password reset token is invalid.'], status: 422, description: 'Invalid token')]
    public function __invoke(ResetPasswordRequest $request): JsonResponse
    {
        $payload = ResetPasswordPayload::fromRequest($request);
        $resetUserId = null;

        $status = Password::broker()->reset(
            [
                'email' => $payload->email,
                'password' => $payload->password,
                'password_confirmation' => $payload->passwordConfirmation,
                'token' => $payload->token,
            ],
            function (User $user, string $password) use (&$resetUserId): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                $resetUserId = (string) $user->getKey();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            SecurityAudit::log('auth.password_reset.failed', [
                'email_hash' => SecurityAudit::hashEmail($payload->email),
            ]);

            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        SecurityAudit::log('auth.password_reset.succeeded', [
            'user_id' => $resetUserId,
        ]);

        return response()->json(['message' => 'Your password has been reset.']);
    }
}
