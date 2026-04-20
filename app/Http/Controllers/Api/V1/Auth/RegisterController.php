<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Payloads\V1\Auth\RegisterPayload;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\User\UserResource;
use App\Models\User;
use App\Support\SecurityAudit;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Authentication', 'Register, login, and manage tokens')]
final class RegisterController
{
    #[Unauthenticated]
    #[Response([
        'data' => ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
        'meta' => ['token' => '1|abc123...', 'token_type' => 'Bearer'],
    ], status: 201, description: 'Registration successful')]
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $payload = RegisterPayload::fromRequest($request);

        $user = User::create([
            'name' => $payload->name,
            'email' => $payload->email,
            'password' => $payload->password,
        ]);

        event(new Registered($user));

        $token = $user->createToken('api')->plainTextToken;

        SecurityAudit::log('auth.register.succeeded', [
            'user_id' => (string) $user->getKey(),
        ]);

        return response()->json([
            'data' => new UserResource($user),
            'meta' => [
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }
}
