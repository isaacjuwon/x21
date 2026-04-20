<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Payloads\V1\Auth\LoginPayload;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Api\V1\User\UserResource;
use App\Models\User;
use App\Support\SecurityAudit;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class LoginController
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $payload = LoginPayload::fromRequest($request);

        $user = User::query()->where('email', $payload->email)->first();

        if (! $user || ! Hash::check($payload->password, $user->password)) {
            SecurityAudit::log('auth.login.failed', [
                'email_hash' => SecurityAudit::hashEmail($payload->email),
            ]);

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Generate a plain token, store its hash
        $plainToken = Str::random(60);
        $user->forceFill(['api_token' => hash('sha256', $plainToken)])->save();

        SecurityAudit::log('auth.login.succeeded', [
            'user_id' => (string) $user->getKey(),
        ]);

        return response()->json([
            'data' => new UserResource($user),
            'meta' => [
                'token' => $plainToken,
                'token_type' => 'Bearer',
            ],
        ]);
    }
}
