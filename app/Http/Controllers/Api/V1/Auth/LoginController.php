<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Resources\Api\V1\User\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Authentication', 'Obtain and revoke Sanctum tokens')]
#[Unauthenticated]
class LoginController
{
    #[BodyParam('email', 'string', description: 'User email address', required: true, example: 'user@example.com')]
    #[BodyParam('password', 'string', description: 'User password', required: true, example: 'password')]
    #[BodyParam('device_name', 'string', description: 'Device name for the token (e.g. mobile, web)', required: true, example: 'mobile')]
    #[Response([
        'data' => [
            'token' => '1|abc123...',
            'user' => ['id' => 1, 'name' => 'John Doe', 'email' => 'user@example.com'],
        ],
    ], status: 200, description: 'Login successful')]
    #[Response(['message' => 'The provided credentials are incorrect.'], status: 422)]
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:255'],
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 422);
        }

        $user = Auth::user();
        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'user' => new UserResource($user),
            ],
        ]);
    }
}
