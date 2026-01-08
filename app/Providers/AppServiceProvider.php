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
            ]);
        });
    }
}
