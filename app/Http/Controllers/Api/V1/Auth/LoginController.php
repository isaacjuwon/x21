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
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Authentication', 'Register, login, and manage tokens')]
final class LoginController
{
    #[Unauthenticated]
    #[Response([
        'data' => ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
        'meta' => ['token' => '1|abc123...', 'token_type' => 'Bearer'],
    ], status: 200, description: 'Login successful')]
    #[Response(['message' => 'The provided credentials are incorrect.'], status: 422, description: 'Invalid credentials')]
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

        $token = $user->createToken('api')->plainTextToken;

        SecurityAudit::log('auth.login.succeeded', [
            'user_id' => (string) $user->getKey(),
        ]);

        return response()->json([
            'data' => new UserResource($user),
            'meta' => [
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }
}
