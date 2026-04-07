<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Payloads\V1\Auth\LoginPayload;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Api\V1\User\UserResource;
use App\Models\User;
use App\Support\SecurityAudit;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
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
                'device_name' => $payload->deviceName,
            ]);

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        [$token, $expiresAt] = $this->issueToken($user, $payload->deviceName);

        SecurityAudit::log('auth.login.succeeded', [
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
        ]);
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
