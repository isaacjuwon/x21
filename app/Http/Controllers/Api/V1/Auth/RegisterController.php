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
use Illuminate\Support\Carbon;

final class RegisterController
{
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $payload = RegisterPayload::fromRequest($request);

        $user = User::create([
            'name' => $payload->name,
            'email' => $payload->email,
            'password' => $payload->password,
        ]);

        event(new Registered($user));

        [$token, $expiresAt] = $this->issueToken($user, $payload->deviceName);

        SecurityAudit::log('auth.register.succeeded', [
            'user_id' => (string) $user->getKey(),
            'device_name' => $payload->deviceName,
        ]);

        return response()->json([
            'data' => new UserResource($user),
            'meta' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt?->toAtomString(),
            ],
        ], 201);
    }

    /**
     * @return array{0:string,1:Carbon|null}
     */
    private function issueToken(User $user, string $deviceName): array
    {
        $configuredExpiration = config('sanctum.expiration');
        $expirationMinutes = filter_var(
            $configuredExpiration,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]],
        );

        $expiresAt = $expirationMinutes !== false
            ? now()->addMinutes($expirationMinutes)
            : null;

        $token = $user->createToken($deviceName, ['*'], $expiresAt);

        return [$token->plainTextToken, $expiresAt];
    }
}
