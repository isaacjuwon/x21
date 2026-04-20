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
use Illuminate\Support\Str;

final class RegisterController
{
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $payload = RegisterPayload::fromRequest($request);

        $plainToken = Str::random(60);

        $user = User::create([
            'name' => $payload->name,
            'email' => $payload->email,
            'password' => $payload->password,
            'api_token' => hash('sha256', $plainToken),
        ]);

        event(new Registered($user));

        SecurityAudit::log('auth.register.succeeded', [
            'user_id' => (string) $user->getKey(),
        ]);

        return response()->json([
            'data' => new UserResource($user),
            'meta' => [
                'token' => $plainToken,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }
}
