<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Public Auth Routes
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::middleware(['auth:sanctum'])->group(function () {
    // Auth Routes
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('api.profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('api.profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('api.profile.password');

    // User Routes
    Route::get('/user', [UserController::class, 'show'])->name('api.user.show');
    Route::put('/user', [UserController::class, 'update'])->name('api.user.update');

    // Share Routes
    Route::get('/shares', [\App\Http\Controllers\Api\ShareController::class, 'index'])->name('api.shares.index');
    Route::post('/shares/buy', [\App\Http\Controllers\Api\ShareController::class, 'buy'])->name('api.shares.buy');
    Route::post('/shares/sell', [\App\Http\Controllers\Api\ShareController::class, 'sell'])->name('api.shares.sell');

    // Loan Routes
    Route::get('/loans', [\App\Http\Controllers\Api\LoanController::class, 'index'])->name('api.loans.index');
    Route::post('/loans/apply', [\App\Http\Controllers\Api\LoanController::class, 'apply'])->name('api.loans.apply');
    Route::post('/loans/{loan}/repay', [\App\Http\Controllers\Api\LoanController::class, 'repay'])->name('api.loans.repay');

    // Wallet Routes
    Route::get('/wallet', [\App\Http\Controllers\Api\WalletController::class, 'index'])->name('api.wallet.index');
    Route::post('/wallet/deposit', [\App\Http\Controllers\Api\WalletController::class, 'deposit'])->name('api.wallet.deposit');
    Route::post('/wallet/fund', [\App\Http\Controllers\Api\WalletController::class, 'fund'])->name('api.wallet.fund');
    Route::post('/wallet/withdraw', [\App\Http\Controllers\Api\WalletController::class, 'withdraw'])->name('api.wallet.withdraw');
    Route::post('/wallet/transfer', [\App\Http\Controllers\Api\WalletController::class, 'transfer'])->name('api.wallet.transfer');
    Route::get('/banks', [\App\Http\Controllers\Api\WalletController::class, 'getBanks'])->name('api.banks.index');

    // KYC Routes
    Route::get('/kyc', [\App\Http\Controllers\Api\KycController::class, 'index'])->name('api.kyc.index');
    Route::post('/kyc/verify', [\App\Http\Controllers\Api\KycController::class, 'verify'])->name('api.kyc.verify');

    // Payment Routes
    Route::get('/payment/verify/{reference}', [\App\Http\Controllers\Api\PaymentController::class, 'verify'])->name('api.payment.verify');
});
