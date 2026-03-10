<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            \Laravel\Fortify\Contracts\LoginResponse::class,
            \App\Http\Responses\LoginResponse::class
        );

        $this->app->singleton(
            \Laravel\Fortify\Contracts\RegisterResponse::class,
            \App\Http\Responses\RegisterResponse::class
        );

        $this->app->singleton(
            \Laravel\Fortify\Contracts\LogoutResponse::class,
            \App\Http\Responses\LogoutResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::authenticateUsing(function (Request $request) {
            $usernameField = Fortify::username();
            $username = $request->input($usernameField);
            $password = $request->input('password');

            \Illuminate\Support\Facades\Log::info('API Login Attempt', [
                'username_field' => $usernameField,
                'username' => $username,
                'has_password' => ! empty($password),
                'is_json' => $request->expectsJson(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
            ]);

            $user = User::where($usernameField, Str::lower($username))->first();

            if ($user && \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
                \Illuminate\Support\Facades\Log::info('API Login Success', ['user_id' => $user->id]);

                return $user;
            }

            \Illuminate\Support\Facades\Log::warning('API Login Failure', [
                'user_found' => ! is_null($user),
                'username' => $username,
            ]);

            return null;
        });

    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::verifyEmailView(fn () => view('auth.verify-email'));
        Fortify::twoFactorChallengeView(fn () => view('auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn () => view('auth.confirm-password'));
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::resetPasswordView(fn () => view('auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
