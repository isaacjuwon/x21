<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Loans\LoanApprovalController;
use App\Http\Controllers\Api\V1\Loans\LoanController;
use App\Http\Controllers\Api\V1\Loans\LoanDisbursementController;
use App\Http\Controllers\Api\V1\Loans\LoanEligibilityController;
use App\Http\Controllers\Api\V1\Loans\LoanRejectionController;
use App\Http\Controllers\Api\V1\Loans\LoanRepaymentController;
use App\Http\Controllers\Api\V1\Loans\LoanScheduleController;
use App\Http\Controllers\Api\V1\Services\AirtimeController;
use App\Http\Controllers\Api\V1\Services\CableTvController;
use App\Http\Controllers\Api\V1\Services\DataController;
use App\Http\Controllers\Api\V1\Services\ElectricityController;
use App\Http\Controllers\Api\V1\Shares\DividendController;
use App\Http\Controllers\Api\V1\Shares\DividendPayoutController;
use App\Http\Controllers\Api\V1\Shares\ShareHoldingController;
use App\Http\Controllers\Api\V1\Shares\ShareListingController;
use App\Http\Controllers\Api\V1\Shares\ShareOrderApprovalController;
use App\Http\Controllers\Api\V1\Shares\ShareOrderController;
use App\Http\Controllers\Api\V1\Shares\ShareOrderRejectionController;
use App\Http\Controllers\Api\V1\Shares\SharePriceHistoryController;
use App\Http\Controllers\Api\V1\User\UserController;
use App\Http\Controllers\Api\V1\Wallet\TransactionController;
use App\Http\Controllers\Api\V1\Wallet\WalletController;
use App\Http\Controllers\Api\V1\Wallet\WalletFundController;
use App\Http\Controllers\Api\V1\Wallet\WalletTransferController;
use App\Http\Controllers\Api\V1\Wallet\WalletWithdrawController;
use App\Http\Controllers\WebhookController;
use App\Http\Middleware\RequestId;
use Illuminate\Support\Facades\Route;

// Webhooks — no auth, no rate limiting
Route::post('webhooks/{provider}', [WebhookController::class, 'handle'])
    ->name('webhooks');

// --- Auth (Sanctum token issuance) ---
Route::prefix('v1/auth')->name('v1.auth.')->group(function () {
    Route::post('/register', RegisterController::class)->name('register')->middleware('throttle:10,1');
    Route::post('/login', LoginController::class)->name('login')->middleware('throttle:10,1');
    Route::post('/logout', LogoutController::class)->name('logout')->middleware('auth:sanctum');
    Route::get('/me', MeController::class)->name('me')->middleware('auth:sanctum');
});

// V1 API
Route::middleware(['auth:sanctum', RequestId::class])
    ->prefix('v1')
    ->name('v1.')
    ->group(function () {

        // --- User ---
        Route::prefix('user')->name('user.')->group(function () {
            Route::get('/', [UserController::class, 'show'])->name('show');
            Route::patch('/', [UserController::class, 'update'])->name('update');
        });

        // --- Wallet ---
        Route::prefix('wallet')->name('wallet.')->group(function () {
            Route::get('/', WalletController::class)->name('show');
            Route::post('/fund', [WalletFundController::class, 'initialize'])->name('fund.initialize');
            Route::post('/fund/verify', [WalletFundController::class, 'verify'])->name('fund.verify');
            Route::post('/transfer', WalletTransferController::class)->name('transfer');
            Route::post('/withdraw', WalletWithdrawController::class)->name('withdraw');
            Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
            Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        });

        // --- Loans ---
        Route::prefix('loans')->name('loans.')->group(function () {
            Route::get('/', [LoanController::class, 'index'])->name('index');
            Route::post('/', [LoanController::class, 'store'])
                ->middleware('throttle:loan-applications')
                ->name('store');
            Route::get('/{loan}', [LoanController::class, 'show'])->name('show');
            Route::get('/{loan}/schedule', LoanScheduleController::class)->name('schedule');
            Route::post('/{loan}/repayments', LoanRepaymentController::class)->name('repayments.store');
            Route::post('/{loan}/approve', LoanApprovalController::class)->name('approve');
            Route::post('/{loan}/disburse', LoanDisbursementController::class)->name('disburse');
            Route::post('/{loan}/reject', LoanRejectionController::class)->name('reject');
        });

        Route::post('loans/eligibility', LoanEligibilityController::class)->name('loans.eligibility');

        // --- Shares ---
        Route::prefix('shares')->name('shares.')->group(function () {
            Route::get('/listing', [ShareListingController::class, 'show'])->name('listing.show');
            Route::put('/listing/price', [ShareListingController::class, 'update'])->name('listing.update');

            Route::get('/orders', [ShareOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [ShareOrderController::class, 'show'])->name('orders.show');
            Route::post('/orders/buy', [ShareOrderController::class, 'buy'])->name('orders.buy');
            Route::post('/orders/sell', [ShareOrderController::class, 'sell'])->name('orders.sell');
            Route::post('/orders/{order}/approve', ShareOrderApprovalController::class)->name('orders.approve');
            Route::post('/orders/{order}/reject', ShareOrderRejectionController::class)->name('orders.reject');

            Route::get('/holdings', ShareHoldingController::class)->name('holdings.show');
            Route::get('/price-history', SharePriceHistoryController::class)->name('price-history.index');

            Route::post('/dividends', DividendController::class)->name('dividends.store');
            Route::get('/dividends/payouts', DividendPayoutController::class)->name('dividends.payouts');
        });

        // --- Services (VTU) ---
        Route::prefix('services')->name('services.')->group(function () {
            Route::post('/airtime', AirtimeController::class)->name('airtime');
            Route::post('/data', DataController::class)->name('data');
            Route::post('/electricity', ElectricityController::class)->name('electricity');
            Route::post('/cable-tv', CableTvController::class)->name('cable-tv');
        });
    });
