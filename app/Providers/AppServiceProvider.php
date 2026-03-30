<?php

namespace App\Providers;

use App\Loans\LoanEligibilityChecker;
use App\Loans\Specifications\UserDurationSpecification;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

use App\Managers\ApiManager;
use App\Settings\GeneralSettings;
use App\Settings\LayoutSettings;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ApiManager::class, fn ($app) => new ApiManager($app));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureCurrency();
        $this->configureRateLimiting();
        $this->shareSettings();
    }

    /**
     * Configure default currency using Number facade.
     */
    protected function configureCurrency(): void
    {
        Number::useCurrency(app(GeneralSettings::class)->currency ?? 'NGN');
    }

    /**
     * Share settings with all views.
     */
    protected function shareSettings(): void
    {
        View::composer('*', function ($view) {
            $view->with([
                'generalSettings' => app(GeneralSettings::class),
                'layoutSettings' => app(LayoutSettings::class),
            ]);
        });
    }

    /**
     * Configure rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('loan-applications', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
