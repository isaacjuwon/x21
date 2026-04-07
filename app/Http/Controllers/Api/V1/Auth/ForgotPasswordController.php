<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Payloads\V1\Auth\ForgotPasswordPayload;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Support\SecurityAudit;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;

final class ForgotPasswordController
{
    public function __invoke(ForgotPasswordRequest $request): JsonResponse
    {
        $payload = ForgotPasswordPayload::fromRequest($request);

        Password::broker()->sendResetLink(['email' => $payload->email]);

        SecurityAudit::log('auth.password_reset.requested', [
            'email_hash' => SecurityAudit::hashEmail($payload->email),
        ]);

        return response()->json([
            'message' => 'If the account exists, a password reset link has been sent.',
        ]);
    }
}
