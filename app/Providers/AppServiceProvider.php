<?php

namespace App\Providers;

use App\Events\Services\ServicePurchased;
use App\Events\Wallets\TransactionFailed;
use App\Events\Wallets\WalletWithdrawn;
use App\Integrations\Dojah\DojahConnector;
use App\Integrations\Epins\EpinsConnector;
use App\Integrations\KudiSms\KudiSmsConnector;
use App\Integrations\Paystack\PaystackConnector;
use App\Listeners\Services\SendServicePurchasedNotificationListener;
use App\Listeners\Wallets\DispatchWalletReversalListener;
use App\Listeners\Wallets\SendWalletWithdrawnNotificationListener;
use App\Managers\ApiManager;
use App\Models\Faq;
use App\Notifications\Channels\KudiSmsChannel;
use App\Settings\GeneralSettings;
use App\Settings\LayoutSettings;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\LaravelSettings\Exceptions\MissingSettings;
use stdClass;

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
        $this->registerNotificationChannels();
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
        KudiSmsConnector::register($this->app);
    }

    /**
     * Register custom notification channels.
     */
    protected function registerNotificationChannels(): void
    {
        $this->app->make(ChannelManager::class)->extend('kudisms', function ($app) {
            return $app->make(KudiSmsChannel::class);
        });
    }

    protected function registerEventListeners(): void
    {
        Event::listen(TransactionFailed::class, DispatchWalletReversalListener::class);
        Event::listen(ServicePurchased::class, SendServicePurchasedNotificationListener::class);
        Event::listen(WalletWithdrawn::class, SendWalletWithdrawnNotificationListener::class);
    }

    /**
     * Configure default currency using Number facade.
     */
    protected function configureCurrency(): void
    {
        try {
            $currency = app(GeneralSettings::class)->currency ?? 'NGN';
        } catch (MissingSettings|QueryException) {
            $currency = 'NGN';
        }

        Number::useCurrency($currency);
        Number::useLocale('en_NG');
    }

    /**
     * Share settings with all views.
     */
    protected function shareSettings(): void
    {
        View::composer('*', function ($view) {
            $view->with([
                'generalSettings' => $this->resolveGeneralSettings(),
                'layoutSettings' => $this->resolveLayoutSettings(),
            ]);
        });

        View::composer('landing', function ($view) {
            $view->with('faqs', Faq::active()->get());
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

    private function resolveGeneralSettings(): GeneralSettings|stdClass
    {
        try {
            return app(GeneralSettings::class);
        } catch (MissingSettings|QueryException) {
            return (object) [
                'site_name' => config('app.name'),
                'site_logo' => null,
                'site_dark_logo' => null,
                'site_favicon' => null,
                'site_dark_favicon' => null,
                'site_description' => null,
                'contact_email' => null,
                'support_email' => null,
                'maintenance_mode' => false,
                'registration_enabled' => true,
                'currency' => 'NGN',
                'timezone' => config('app.timezone', 'Africa/Lagos'),
            ];
        }
    }

    private function resolveLayoutSettings(): LayoutSettings|stdClass
    {
        try {
            return app(LayoutSettings::class);
        } catch (MissingSettings|QueryException) {
            return (object) [
                'primary_color' => '#2563eb',
                'sidebar_collapsible' => true,
                'font_family' => 'Instrument Sans',
                'homepage_features_title' => 'Why choose us',
                'homepage_features_description' => null,
                'homepage_features_items' => [],
                'banner' => null,
                'about' => null,
                'address' => null,
                'facebook' => null,
                'twitter' => null,
                'instagram' => null,
                'email' => null,
                'homepage_title' => config('app.name'),
                'homepage_description' => null,
            ];
        }
    }
}
