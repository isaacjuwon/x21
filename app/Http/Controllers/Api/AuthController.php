<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends ApiController
{
    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign default 'user' role
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('user');
        }

        $tokenStr = '';
        $tokenResult = $user->createToken('auth_token');

        if (isset($tokenResult->plainTextToken)) {
            $tokenStr = $tokenResult->plainTextToken;
        } elseif (isset($tokenResult->accessToken)) {
            $tokenStr = $tokenResult->accessToken;
        } else {
            // Fallback or string
            $tokenStr = (string) $tokenResult;
        }

        return $this->createdResponse([
            'user' => $user,
            'access_token' => $tokenStr,
            'token_type' => 'Bearer',
        ], 'User registered successfully');
    }

    /**
     * Login user and create token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->unauthorizedResponse('Invalid credentials');
        }

        $tokenStr = '';
        $tokenResult = $user->createToken('auth_token');

        if (isset($tokenResult->plainTextToken)) {
            $tokenStr = $tokenResult->plainTextToken;
        } elseif (isset($tokenResult->accessToken)) {
            $tokenStr = $tokenResult->accessToken;
        } else {
            $tokenStr = (string) $tokenResult;
        }

        $response = [
            'user' => $user,
            'access_token' => $tokenStr,
            'token_type' => 'Bearer',
        ];

        if (method_exists($user, 'getRoleNames')) {
            $response['roles'] = $user->getRoleNames();
        }
        if (method_exists($user, 'getAllPermissions')) {
            $response['permissions'] = $user->getAllPermissions()->pluck('name');
        }

        return $this->successResponse($response, 'Login successful');
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(Request $request): JsonResponse
    {
        // Handle Sanctum or Passport
        $user = $request->user();
        if ($user) {
            if (method_exists($user->currentAccessToken(), 'delete')) {
                // Sanctum
                $user->currentAccessToken()->delete();
            } elseif (method_exists($user->token(), 'revoke')) {
                // Passport
                $user->token()->revoke();
            }
        }

        return $this->successResponse(null, 'Logged out successfully');
    }
}
