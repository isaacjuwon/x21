<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            $user = $request->user();

            $accessToken = $user->createToken('access_token', ['*'], now()->addMinutes(60))->plainTextToken;
            $refreshToken = $user->createToken('refresh_token', ['auth:refresh'], now()->addDays(30))->plainTextToken;

            return new JsonResponse([
                'user' => $user,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ], 201);
        }

        return redirect()->intended(config('fortify.home'));
    }
}
