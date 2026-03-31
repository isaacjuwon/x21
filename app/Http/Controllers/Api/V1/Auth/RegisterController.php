<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Resources\Api\V1\User\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Authentication', 'Obtain and revoke Sanctum tokens')]
#[Unauthenticated]
class RegisterController
{
    #[BodyParam('name', 'string', description: 'Full name', required: true, example: 'John Doe')]
    #[BodyParam('email', 'string', description: 'Email address', required: true, example: 'user@example.com')]
    #[BodyParam('password', 'string', description: 'Password (min 8 chars)', required: true, example: 'password')]
    #[BodyParam('password_confirmation', 'string', description: 'Password confirmation', required: true, example: 'password')]
    #[BodyParam('device_name', 'string', description: 'Device name for the token', required: true, example: 'mobile')]
    #[Response([
        'data' => [
            'token' => '1|abc123...',
            'user' => ['id' => 1, 'name' => 'John Doe', 'email' => 'user@example.com'],
        ],
    ], status: 201, description: 'Registration successful')]
    #[Response(['message' => 'The email has already been taken.'], status: 422)]
    public function __invoke(Request $request, CreateNewUser $createUser): JsonResponse
    {
        $request->validate([
            'device_name' => ['required', 'string', 'max:255'],
        ]);

        $user = $createUser->create($request->all());

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'user' => new UserResource($user),
            ],
        ], 201);
    }
}
