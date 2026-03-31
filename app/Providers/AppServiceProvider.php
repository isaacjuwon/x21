<?php

namespace App\Providers;

use App\Events\Wallets\TransactionFailed;
use App\Integrations\Dojah\DojahConnector;
use App\Integrations\Epins\EpinsConnector;
use App\Integrations\Paystack\PaystackConnector;
use App\Listeners\Wallets\DispatchWalletReversalListener;
use App\Managers\ApiManager;
use App\Settings\GeneralSettings;
use App\Settings\LayoutSettings;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->registerConnectors();
        $this->registerEventListeners();
        $this->configureDefaults();
        $this->configureCurrency();
        $this->configureRateLimiting();
        $this->shareSettings();
    }

    /**
     * Register integration connectors.
     */
    protected function registerConnectors(): void
    {
        PaystackConnector::register($this->app);
        EpinsConnector::register($this->app);
        DojahConnector::register($this->app);
    }

    protected function registerEventListeners(): void
    {
        Event::listen(TransactionFailed::class, DispatchWalletReversalListener::class);
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
