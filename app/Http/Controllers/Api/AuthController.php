<?php

namespace App\Http\Controllers\Api;

use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class AuthController extends ApiController
{
    /**
     * Refresh the access token.
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $user->currentAccessToken();

        // Ensure the token has the refresh ability
        if (! $token->can('auth:refresh')) {
            return $this->forbiddenResponse('Invalid token for refreshing');
        }

        // Revoke the old refresh token (and potentially existing access tokens)
        $token->delete();

        // Issue new tokens
        $accessToken = $user->createToken('access_token', ['*'], now()->addMinutes(60))->plainTextToken;
        $refreshToken = $user->createToken('refresh_token', ['auth:refresh'], now()->addDays(30))->plainTextToken;

        return $this->successResponse([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], 'Token refreshed successfully');
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * Send a reset link to the given user.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? $this->successResponse(null, __($status))
            : $this->errorResponse(__($status), 422);
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(Request $request, ResetUserPassword $reseter): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($reseter, $request) {
                $reseter->reset($user, $request->only('password', 'password_confirmation'));
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? $this->successResponse(null, __($status))
            : $this->errorResponse(__($status), 422);
    }

    /**
     * Verify the user's email.
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->route('id'));

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return $this->errorResponse('Invalid verification link.', 403);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->successResponse(null, 'Email already verified.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->successResponse(null, 'Email verified successfully.');
    }

    /**
     * Resend verification email.
     */
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->successResponse(null, 'Email already verified.');
        }

        $request->user()->sendEmailVerificationNotification();

        return $this->successResponse(null, 'Verification link sent.');
    }
}
