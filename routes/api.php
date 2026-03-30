<?php

use App\Http\Controllers\Api\V1\LoanApprovalController;
use App\Http\Controllers\Api\V1\LoanController;
use App\Http\Controllers\Api\V1\LoanDisbursementController;
use App\Http\Controllers\Api\V1\LoanEligibilityController;
use App\Http\Controllers\Api\V1\LoanRejectionController;
use App\Http\Controllers\Api\V1\LoanRepaymentController;
use App\Http\Controllers\Api\V1\LoanScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::middleware('throttle:loan-applications')->post('loans', [LoanController::class, 'store']);

    Route::get('loans', [LoanController::class, 'index']);
    Route::get('loans/{loan}', [LoanController::class, 'show']);

    Route::post('loans/eligibility', [LoanEligibilityController::class, 'store']);
    Route::get('loans/{loan}/schedule', [LoanScheduleController::class, 'index']);
    Route::post('loans/{loan}/repayments', [LoanRepaymentController::class, 'store']);
    Route::post('loans/{loan}/approve', [LoanApprovalController::class, 'store']);
    Route::post('loans/{loan}/disburse', [LoanDisbursementController::class, 'store']);
    Route::post('loans/{loan}/reject', [LoanRejectionController::class, 'store']);
});
