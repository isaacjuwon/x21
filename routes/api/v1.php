<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\DeleteAllTokensController;
use App\Http\Controllers\Api\V1\Auth\DeleteTokenController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\ListTokensController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\Kyc\AutomaticController as KycAutomaticController;
use App\Http\Controllers\Api\V1\Kyc\IndexController as KycIndexController;
use App\Http\Controllers\Api\V1\Kyc\ManualController as KycManualController;
use App\Http\Controllers\Api\V1\Loans\IndexController as LoansIndexController;
use App\Http\Controllers\Api\V1\Loans\LoanApprovalController;
use App\Http\Controllers\Api\V1\Loans\LoanDisbursementController;
use App\Http\Controllers\Api\V1\Loans\LoanEligibilityController;
use App\Http\Controllers\Api\V1\Loans\LoanRejectionController;
use App\Http\Controllers\Api\V1\Loans\LoanRepaymentController;
use App\Http\Controllers\Api\V1\Loans\LoanScheduleController;
use App\Http\Controllers\Api\V1\Loans\ShowController as LoansShowController;
use App\Http\Controllers\Api\V1\Loans\StoreController as LoansStoreController;
use App\Http\Controllers\Api\V1\Services\AirtimeController;
use App\Http\Controllers\Api\V1\Services\CableTvController;
use App\Http\Controllers\Api\V1\Services\DataController;
use App\Http\Controllers\Api\V1\Services\ElectricityController;
use App\Http\Controllers\Api\V1\Shares\BuyOrderController;
use App\Http\Controllers\Api\V1\Shares\DividendController;
use App\Http\Controllers\Api\V1\Shares\DividendPayoutController;
use App\Http\Controllers\Api\V1\Shares\IndexOrdersController;
use App\Http\Controllers\Api\V1\Shares\SellOrderController;
use App\Http\Controllers\Api\V1\Shares\ShareHoldingController;
use App\Http\Controllers\Api\V1\Shares\ShareOrderApprovalController;
use App\Http\Controllers\Api\V1\Shares\ShareOrderRejectionController;
use App\Http\Controllers\Api\V1\Shares\SharePriceHistoryController;
use App\Http\Controllers\Api\V1\Shares\ShowListingController;
use App\Http\Controllers\Api\V1\Shares\ShowOrderController;
use App\Http\Controllers\Api\V1\Shares\UpdateListingController;
use App\Http\Controllers\Api\V1\Support\AiSupportController;
use App\Http\Controllers\Api\V1\Support\IndexFaqsController;
use App\Http\Controllers\Api\V1\Tickets\IndexController as TicketsIndexController;
use App\Http\Controllers\Api\V1\Tickets\ShowController as TicketsShowController;
use App\Http\Controllers\Api\V1\Tickets\StoreController as TicketsStoreController;
use App\Http\Controllers\Api\V1\Tickets\StoreReplyController;
use App\Http\Controllers\Api\V1\User\ShowController as UserShowController;
use App\Http\Controllers\Api\V1\User\UpdateController as UserUpdateController;
use App\Http\Controllers\Api\V1\Wallet\BankListController;
use App\Http\Controllers\Api\V1\Wallet\IndexTransactionsController;
use App\Http\Controllers\Api\V1\Wallet\ShowTransactionController;
use App\Http\Controllers\Api\V1\Wallet\VerifyAccountController;
use App\Http\Controllers\Api\V1\Wallet\WalletController;
use App\Http\Controllers\Api\V1\Wallet\WalletFundController;
use App\Http\Controllers\Api\V1\Wallet\WalletTransferController;
use App\Http\Controllers\Api\V1\Wallet\WalletWithdrawController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Webhooks — no auth, no rate limiting
Route::post('/webhooks/{provider}', [WebhookController::class, 'handle'])
    ->name('webhooks');

// --- Auth ---
Route::prefix('/auth')->name('auth.')->group(function (): void {
    Route::post('/register', RegisterController::class)
        ->middleware(['idempotency', 'throttle:10,1'])
        ->name('register');
    Route::post('/login', LoginController::class)
        ->middleware('throttle:10,1')
        ->name('login');
    Route::post('/forgot-password', ForgotPasswordController::class)
        ->middleware(['idempotency', 'throttle:5,1'])
        ->name('password.forgot');
    Route::post('/reset-password', ResetPasswordController::class)
        ->middleware('throttle:5,1')
        ->name('password.reset');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/me', MeController::class)->name('me');
        Route::post('/logout', LogoutController::class)->name('logout');
        Route::get('/tokens', ListTokensController::class)->name('tokens.index');
        Route::delete('/tokens', DeleteAllTokensController::class)->name('tokens.destroy-all');
        Route::delete('/tokens/{token_id}', DeleteTokenController::class)->name('tokens.destroy');
    });
});

// --- Authenticated V1 routes ---
Route::middleware('auth:sanctum')->group(function (): void {

    // --- User ---
    Route::prefix('/user')->name('user.')->group(function (): void {
        Route::get('/', UserShowController::class)->name('show');
        Route::patch('/', UserUpdateController::class)->name('update');
    });

    // --- Wallet ---
    Route::prefix('/wallet')->name('wallet.')->group(function (): void {
        Route::get('/', WalletController::class)->name('show');
        Route::post('/fund', [WalletFundController::class, 'initialize'])
            ->middleware('idempotency')
            ->name('fund.initialize');
        Route::post('/fund/verify', [WalletFundController::class, 'verify'])->name('fund.verify');
        Route::post('/transfer', WalletTransferController::class)
            ->middleware('idempotency')
            ->name('transfer');
        Route::post('/withdraw', WalletWithdrawController::class)
            ->middleware('idempotency')
            ->name('withdraw');
        Route::get('/banks', BankListController::class)->name('banks');
        Route::get('/verify-account', VerifyAccountController::class)->name('verify-account');
        Route::get('/transactions', IndexTransactionsController::class)->name('transactions.index');
        Route::get('/transactions/{transaction}', ShowTransactionController::class)->name('transactions.show');
    });

    // --- Loans ---
    Route::post('/loans/eligibility', LoanEligibilityController::class)->name('loans.eligibility');

    Route::prefix('/loans')->name('loans.')->group(function (): void {
        Route::get('/', LoansIndexController::class)->name('index');
        Route::post('/', LoansStoreController::class)
            ->middleware(['idempotency', 'throttle:loan-applications'])
            ->name('store');
        Route::get('/{loan}', LoansShowController::class)->name('show');
        Route::get('/{loan}/schedule', LoanScheduleController::class)->name('schedule');
        Route::post('/{loan}/repayments', LoanRepaymentController::class)
            ->middleware('idempotency')
            ->name('repayments.store');
        Route::post('/{loan}/approve', LoanApprovalController::class)->name('approve');
        Route::post('/{loan}/disburse', LoanDisbursementController::class)->name('disburse');
        Route::post('/{loan}/reject', LoanRejectionController::class)->name('reject');
    });

    // --- Shares ---
    Route::prefix('/shares')->name('shares.')->group(function (): void {
        Route::get('/listing', ShowListingController::class)->name('listing.show');
        Route::put('/listing/price', UpdateListingController::class)->name('listing.update');

        Route::get('/orders', IndexOrdersController::class)->name('orders.index');
        Route::get('/orders/{order}', ShowOrderController::class)->name('orders.show');
        Route::post('/orders/buy', BuyOrderController::class)
            ->middleware('idempotency')
            ->name('orders.buy');
        Route::post('/orders/sell', SellOrderController::class)
            ->middleware('idempotency')
            ->name('orders.sell');
        Route::post('/orders/{order}/approve', ShareOrderApprovalController::class)->name('orders.approve');
        Route::post('/orders/{order}/reject', ShareOrderRejectionController::class)->name('orders.reject');

        Route::get('/holdings', ShareHoldingController::class)->name('holdings.show');
        Route::get('/price-history', SharePriceHistoryController::class)->name('price-history.index');

        Route::post('/dividends', DividendController::class)
            ->middleware('idempotency')
            ->name('dividends.store');
        Route::get('/dividends/payouts', DividendPayoutController::class)->name('dividends.payouts');
    });

    // --- Services (VTU) ---
    Route::prefix('/services')->name('services.')->group(function (): void {
        Route::post('/airtime', AirtimeController::class)->middleware('idempotency')->name('airtime');
        Route::post('/data', DataController::class)->middleware('idempotency')->name('data');
        Route::post('/electricity', ElectricityController::class)->middleware('idempotency')->name('electricity');
        Route::post('/cable-tv', CableTvController::class)->middleware('idempotency')->name('cable-tv');
    });

    // --- KYC ---
    Route::prefix('/kyc')->name('kyc.')->group(function (): void {
        Route::get('/', KycIndexController::class)->name('index');
        Route::post('/automatic', KycAutomaticController::class)->middleware('idempotency')->name('automatic');
        Route::post('/manual', KycManualController::class)->middleware('idempotency')->name('manual');
    });

    // --- Tickets ---
    Route::prefix('/tickets')->name('tickets.')->group(function (): void {
        Route::get('/', TicketsIndexController::class)->name('index');
        Route::post('/', TicketsStoreController::class)->middleware('idempotency')->name('store');
        Route::get('/{ticket}', TicketsShowController::class)->name('show');
        Route::post('/{ticket}/replies', StoreReplyController::class)
            ->middleware('idempotency')
            ->name('replies.store');
    });

    // --- FAQs ---
    Route::get('/faqs', IndexFaqsController::class)->name('faqs.index');

    // --- AI Support ---
    Route::post('/support/chat', AiSupportController::class)->name('support.chat');
});
