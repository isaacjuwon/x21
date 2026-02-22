<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Public Auth Routes
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('api.password.email');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('api.password.update');

Route::middleware(['auth:sanctum'])->group(function () {
    // Auth Routes
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])
        ->middleware('throttle:6,1')
        ->name('api.verification.send');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('api.verification.verify');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('api.profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('api.profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('api.profile.password');

    // User Routes
    Route::get('/user', [UserController::class, 'show'])->name('api.user.show');
    Route::put('/user', [UserController::class, 'update'])->name('api.user.update');

    // Share Routes
    Route::prefix('shares')->name('api.shares.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\ShareController::class, 'index'])->name('index');
        Route::get('/stats', [\App\Http\Controllers\Api\ShareController::class, 'stats'])->name('stats');
        Route::get('/settings', [\App\Http\Controllers\Api\ShareController::class, 'settings'])->name('settings');
        Route::post('/buy', [\App\Http\Controllers\Api\ShareController::class, 'buy'])->name('buy');
        Route::post('/sell', [\App\Http\Controllers\Api\ShareController::class, 'sell'])->name('sell');
    });

    // Loan Routes
    Route::prefix('loans')->name('api.loans.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\LoanController::class, 'index'])->name('index');
        Route::get('/eligibility', [\App\Http\Controllers\Api\LoanController::class, 'eligibility'])->name('eligibility');
        Route::post('/apply', [\App\Http\Controllers\Api\LoanController::class, 'apply'])->name('apply');
        Route::get('/{loan}', [\App\Http\Controllers\Api\LoanController::class, 'show'])->name('show');
        Route::get('/{loan}/schedule', [\App\Http\Controllers\Api\LoanController::class, 'schedule'])->name('schedule');
        Route::post('/{loan}/repay', [\App\Http\Controllers\Api\LoanController::class, 'repay'])->name('repay');
    });

    // Wallet Routes
    Route::prefix('wallet')->name('api.wallet.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\WalletController::class, 'index'])->name('index');
        Route::post('/deposit', [\App\Http\Controllers\Api\WalletController::class, 'deposit'])->name('deposit');
        Route::post('/fund', [\App\Http\Controllers\Api\WalletController::class, 'fund'])->name('fund');
        Route::post('/withdraw', [\App\Http\Controllers\Api\WalletController::class, 'withdraw'])->name('withdraw');
        Route::post('/transfer', [\App\Http\Controllers\Api\WalletController::class, 'transfer'])->name('transfer');
        Route::get('/banks', [\App\Http\Controllers\Api\WalletController::class, 'getBanks'])->name('banks.index');
    });

    // KYC Routes
    Route::prefix('kyc')->name('api.kyc.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\KycController::class, 'index'])->name('index');
        Route::post('/verify', [\App\Http\Controllers\Api\KycController::class, 'verify'])->name('verify');
    });

    // Payment Routes
    Route::get('/payment/verify/{reference}', [\App\Http\Controllers\Api\PaymentController::class, 'verify'])->name('api.payment.verify');

    // Service Routes
    Route::prefix('services')->name('api.services.')->group(function () {
        // Airtime
        Route::get('/airtime', [\App\Http\Controllers\Api\Service\AirtimeController::class, 'index'])->name('airtime.index');
        Route::post('/airtime', [\App\Http\Controllers\Api\Service\AirtimeController::class, 'store'])->name('airtime');

        // Data
        Route::get('/data', [\App\Http\Controllers\Api\Service\DataController::class, 'index'])->name('data.index');
        Route::post('/data', [\App\Http\Controllers\Api\Service\DataController::class, 'store'])->name('data');

        // Cable
        Route::get('/cable', [\App\Http\Controllers\Api\Service\CableController::class, 'index'])->name('cable.index');
        Route::post('/cable', [\App\Http\Controllers\Api\Service\CableController::class, 'store'])->name('cable');

        // Education
        Route::get('/education', [\App\Http\Controllers\Api\Service\EducationController::class, 'index'])->name('education.index');
        Route::post('/education', [\App\Http\Controllers\Api\Service\EducationController::class, 'store'])->name('education');

        // Electricity
        Route::get('/electricity', [\App\Http\Controllers\Api\Service\ElectricityController::class, 'index'])->name('electricity.index');
        Route::post('/electricity', [\App\Http\Controllers\Api\Service\ElectricityController::class, 'store'])->name('electricity');
    });
});
