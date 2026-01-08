<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::view('/', 'front.index')->name('home');

Route::post('/webhooks/paystack', \App\Http\Controllers\PaystackWebhookController::class)
    ->name('webhooks.paystack');

Route::get('/p/{page:slug}', function (\App\Models\Page $page) {
    if (! $page->is_published && ! auth()->check()) {
        abort(404);
    }

    return view('pages.show', ['page' => $page]);
})->name('pages.show');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    // Settings routes
    Route::livewire('settings/profile', 'settings::profile')->name('profile.edit');
    Route::livewire('settings/password', 'settings::password')->name('user-password.edit');
    Route::livewire('settings/appearance', 'settings::appearance')->name('appearance.edit');

    Route::livewire('settings/two-factor', 'settings::two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    // Dashboard
    Route::livewire('/dashboard', 'pages::dashboard')->name('dashboard');

    // Shares routes
    Route::livewire('/shares', 'pages::shares.index')->name('shares.index');
    Route::livewire('/shares/buy', 'pages::shares.buy')->name('shares.buy');
    Route::livewire('/shares/sell', 'pages::shares.sell')->name('shares.sell');

    // Loan routes
    Route::livewire('/loans', 'pages::loan.index')->name('loan.index');
    Route::livewire('/loans/apply', 'pages::loan.application')->name('loans.apply');
    Route::livewire('/loans/{loan}', 'pages::loan.show')->name('loans.details');
    Route::livewire('/loans/{loan}/payment', 'pages::loan.payment')->name('loans.payment');

    // Service routes
    Route::livewire('/services/airtime', 'pages::services.airtime')->name('airtime');
    Route::livewire('/services/data', 'pages::services.data')->name('data');
    Route::livewire('/services/cable', 'pages::services.cable')->name('cable');
    Route::livewire('/services/electricity', 'pages::services.electricity')->name('electricity');
    Route::livewire('/services/education', 'pages::services.education')->name('education');

    // Wallet routes
    Route::livewire('/wallet', 'pages::wallet.index')->name('wallet.index');
    Route::livewire('/wallet/fund', 'pages::wallet.fund')->name('wallet.fund');
    Route::livewire('/wallet/withdraw', 'pages::wallet.withdraw')->name('wallet.withdraw');
    Route::livewire('/wallet/transfer', 'pages::wallet.transfer')->name('wallet.transfer');

    // Trransaction routes
    Route::livewire('/transactions', 'pages::transactions.index')->name('transactions.index');

    // Analytics
    Route::livewire('/analytics', 'pages::analytics.index')->name('analytics.index');

    // Ticket routes
    Route::livewire('/tickets', 'pages::tickets.index')->name('tickets.index');
    Route::livewire('/tickets/create', 'pages::tickets.create')->name('tickets.create');
    Route::livewire('/tickets/{ticket}', 'pages::tickets.show')->name('tickets.show');

    // Admin Routes - Protected by role middleware
    Route::prefix('admin')->name('admin.')->middleware(['role:super-admin|admin|manager'])->group(function () {
        // Dashboard
        Route::livewire('/', 'admin::index')->name('dashboard');

        // Analytics
        Route::livewire('/analytics', 'admin::analytics.index')->name('analytics.index');

        Route::livewire('/brands', 'admin::brands.index')->name('brands.index');

        // Loans
        Route::livewire('/loans', 'admin::loans.index')->name('loans.index');
        Route::livewire('/loans/create', 'admin::loans.create')->name('loans.create');
        Route::livewire('/loans/{loan}', 'admin::loans.view')->name('loans.view');

        // Loan Levels
        Route::livewire('/loan-levels', 'admin::loan-levels.index')->name('loan-levels.index');
        Route::livewire('/loan-levels/create', 'admin::loan-levels.create')->name('loan-levels.create');
        Route::livewire('/loan-levels/{loanLevel}/edit', 'admin::loan-levels.edit')->name('loan-levels.edit');

        // Shares
        Route::livewire('/shares', 'admin::shares.index')->name('shares.index');
        Route::livewire('/shares/create', 'admin::shares.create')->name('shares.create');
        Route::livewire('/shares/{share}', 'admin::shares.view')->name('shares.view');

        // Dividends
        Route::livewire('/dividends', 'admin::dividends.index')->name('dividends.index');
        Route::livewire('/dividends/create', 'admin::dividends.create')->name('dividends.create');
        Route::livewire('/dividends/{dividend}', 'admin::dividends.view')->name('dividends.view');

        // Transactions
        Route::livewire('/transactions', 'admin::transactions.index')->name('transactions.index');
        Route::livewire('/transactions/{transaction}', 'admin::transactions.view')->name('transactions.view');

        // Utilities
        Route::livewire('/airtime', 'admin::airtime.index')->name('airtime.index');
        Route::livewire('/airtime/create', 'admin::airtime.create')->name('airtime.create');
        Route::livewire('/airtime/{plan}', 'admin::airtime.view')->name('airtime.view');

        Route::livewire('/data', 'admin::data.index')->name('data.index');
        Route::livewire('/data/create', 'admin::data.create')->name('data.create');
        Route::livewire('/data/{plan}', 'admin::data.view')->name('data.view');

        Route::livewire('/cable', 'admin::cable.index')->name('cable.index');
        Route::livewire('/cable/create', 'admin::cable.create')->name('cable.create');
        Route::livewire('/cable/{plan}', 'admin::cable.view')->name('cable.view');

        Route::livewire('/electricity', 'admin::electricity.index')->name('electricity.index');
        Route::livewire('/electricity/create', 'admin::electricity.create')->name('electricity.create');
        Route::livewire('/electricity/{plan}', 'admin::electricity.view')->name('electricity.view');

        Route::livewire('/education', 'admin::education.index')->name('education.index');
        Route::livewire('/education/create', 'admin::education.create')->name('education.create');
        Route::livewire('/education/{plan}', 'admin::education.view')->name('education.view');

        // Users
        Route::livewire('/users', 'admin::users.index')->name('users.index');
        Route::livewire('/users/{user}', 'admin::users.view')->name('users.view');
        Route::livewire('/users/{user}/edit', 'admin::users.edit')->name('users.edit');

        // Wallets
        Route::livewire('/wallets', 'admin::wallets.index')->name('wallets.index');
        Route::livewire('/wallets/{user}', 'admin::wallets.view')->name('wallets.view');

        // Tickets
        Route::livewire('/tickets', 'admin::tickets.index')->name('tickets.index');
        Route::livewire('/tickets/{ticket}', 'admin::tickets.show')->name('tickets.show');

        // Pages
        Route::livewire('/pages', 'admin::pages.index')->name('pages.index');
        Route::livewire('/pages/create', 'admin::pages.create')->name('pages.create');
        Route::livewire('/pages/{page}/edit', 'admin::pages.edit')->name('pages.edit');

        // Roles & Permissions - Only super-admin and admin
        Route::middleware(['role:super-admin|admin'])->group(function () {
            Route::livewire('/roles', 'admin::roles.index')->name('roles.index');
            Route::livewire('/permissions', 'admin::permissions.index')->name('permissions.index');

            // Settings
            Route::redirect('admin/settings', 'admin/settings/general');

            Route::livewire('/settings/general', 'admin::settings.general')->name('settings.general');
            Route::livewire('/settings/shares', 'admin::settings.shares')->name('settings.shares');
            Route::livewire('/settings/loans', 'admin::settings.loans')->name('settings.loans');
            Route::livewire('/settings/wallet', 'admin::settings.wallet')->name('settings.wallet');
            Route::livewire('/settings/layout', 'admin::settings.layout')->name('settings.layout');
        });
    });
});
