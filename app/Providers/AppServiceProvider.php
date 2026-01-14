<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        \App\Enums\Connectors\PaymentConnector::register($this->app);
        \App\Enums\Connectors\VtuConnector::register($this->app);

        \Illuminate\Support\Number::useCurrency('NGN');

        // Share all settings with views
        view()->composer('*', function ($view) {
            $view->with([
                'generalSettings' => app(\App\Settings\GeneralSettings::class),
                'loanSettings' => app(\App\Settings\LoanSettings::class),
                'shareSettings' => app(\App\Settings\ShareSettings::class),
                'walletSettings' => app(\App\Settings\WalletSettings::class),
                'layoutSettings' => app(\App\Settings\LayoutSettings::class),
                'integrationSettings' => app(\App\Settings\IntegrationSettings::class),
            ]);
        });

        // Override config with settings
        try {
            $settings = app(\App\Settings\IntegrationSettings::class);

            config([
                'services.paystack.public_key' => $settings->paystack_public_key,
                'services.paystack.secret_key' => $settings->paystack_secret_key,
                'services.paystack.url' => $settings->paystack_url,

                'services.epins.api_key' => $settings->epins_api_key,
                'services.epins.url' => $settings->epins_url,
                'services.epins.sandbox_url' => $settings->epins_sandbox_url,

                'services.dojah.api_key' => $settings->dojah_api_key,
                'services.dojah.app_id' => $settings->dojah_app_id,
                'services.dojah.base_url' => $settings->dojah_base_url,
            ]);
        } catch (\Exception $e) {
            // Settings table likely doesn't exist yet (migration running)
        }
    }
}
