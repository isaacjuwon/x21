<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        \Illuminate\Support\Facades\Log::info('LoginResponse reached', ['is_json' => $request->wantsJson()]);

        if ($request->wantsJson()) {

            $user = $request->user();

            $accessToken = $user->createToken('access_token', ['*'], now()->addMinutes(60))->plainTextToken;
            $refreshToken = $user->createToken('refresh_token', ['auth:refresh'], now()->addDays(30))->plainTextToken;

            $response = [
                'user' => $user,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ];

            if (method_exists($user, 'getRoleNames')) {
                $response['roles'] = $user->getRoleNames();
            }

            return new JsonResponse($response, 200);
        }

        return redirect()->intended(config('fortify.home'));
    }
}
