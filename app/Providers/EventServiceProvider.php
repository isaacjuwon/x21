<?php

namespace App\Providers;

use App\Listeners\SendLoanNotifications;
use App\Listeners\SendShareNotifications;
use App\Listeners\SendWalletNotifications;
use App\Listeners\SendServiceNotifications;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\Loans\LoanPaymentMade::class => [
            \App\Listeners\CheckUserLoanLevelUpgrade::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        SendLoanNotifications::class,
        SendShareNotifications::class,
        SendWalletNotifications::class,
        SendServiceNotifications::class,
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}
