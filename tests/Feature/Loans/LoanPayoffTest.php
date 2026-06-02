<?php

use App\Actions\Loans\CalculateLoanPayoffAction;
use App\Actions\Loans\PayoffLoanAction;
use App\Enums\Loans\LoanScheduleEntryStatus;
use App\Enums\Loans\LoanStatus;
use App\Enums\Wallets\WalletType;
use App\Models\Loan;
use App\Models\User;
use App\Settings\LoanSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it calculates the payoff quote correctly', function () {
    $user = User::factory()->create();
    $loan = Loan::factory()->create([
        'user_id' => $user->id,
        'principal_amount' => 1000,
        'outstanding_balance' => 1000,
        'interest_rate' => 12,
        'repayment_term_months' => 2,
        'status' => LoanStatus::Disbursed,
        'disbursed_at' => now()->subDays(15),
    ]);

    // Manually create two schedule entries
    $loan->scheduleEntries()->createMany([
        [
            'instalment_number' => 1,
            'due_date' => now()->addDays(15)->toDateString(),
            'instalment_amount' => 510,
            'principal_component' => 500,
            'interest_component' => 10,
            'outstanding_balance' => 500,
            'status' => LoanScheduleEntryStatus::Pending,
            'remaining_amount' => 510,
        ],
        [
            'instalment_number' => 2,
            'due_date' => now()->addMonths(1)->addDays(15)->toDateString(),
            'instalment_amount' => 510,
            'principal_component' => 500,
            'interest_component' => 10,
            'outstanding_balance' => 0,
            'status' => LoanScheduleEntryStatus::Pending,
            'remaining_amount' => 510,
        ],
    ]);

    $settings = app(LoanSettings::class);
    $settings->enable_prepayment_penalty = true;
    $settings->prepayment_penalty_percentage = 50.0;
    $settings->save();

    $action = app(CalculateLoanPayoffAction::class);
    $quote = $action->handle($loan);

    // Math:
    // First installment is active: start = now - 15 days, due = now + 15 days. Total days = 30. Elapsed = 15.
    // Days ratio = 15/30 = 0.5.
    // Accrued interest on first installment = 10 * 0.5 = 5.
    // Unaccrued (future) interest on first installment = 10 * 0.5 = 5.
    // Second installment is future: start = now + 15 days, due = now + 1 month + 15 days.
    // Accrued interest = 0.
    // Unaccrued (future) interest = 10.
    // Total accrued interest = 5 + 0 = 5.
    // Total future interest = 5 + 10 = 15.
    // Prepayment penalty = 15 * 50% = 7.5.
    // Outstanding principal = 500 + 500 = 1000.
    // Total payoff amount = 1000 + 5 + 7.5 = 1012.5.

    expect($quote['remaining_principal'])->toEqual(1000);
    expect($quote['accrued_interest'])->toEqual(5);
    expect($quote['prepayment_penalty'])->toEqual(7.5);
    expect($quote['total_payoff_amount'])->toEqual(1012.5);
});

test('it executes early payoff and debits user wallet', function () {
    $user = User::factory()->create();
    $loan = Loan::factory()->create([
        'user_id' => $user->id,
        'principal_amount' => 1000,
        'outstanding_balance' => 1000,
        'interest_rate' => 12,
        'repayment_term_months' => 2,
        'status' => LoanStatus::Disbursed,
        'disbursed_at' => now()->subDays(15),
    ]);

    $loan->scheduleEntries()->createMany([
        [
            'instalment_number' => 1,
            'due_date' => now()->addDays(15)->toDateString(),
            'instalment_amount' => 510,
            'principal_component' => 500,
            'interest_component' => 10,
            'outstanding_balance' => 500,
            'status' => LoanScheduleEntryStatus::Pending,
            'remaining_amount' => 510,
        ],
        [
            'instalment_number' => 2,
            'due_date' => now()->addMonths(1)->addDays(15)->toDateString(),
            'instalment_amount' => 510,
            'principal_component' => 500,
            'interest_component' => 10,
            'outstanding_balance' => 0,
            'status' => LoanScheduleEntryStatus::Pending,
            'remaining_amount' => 510,
        ],
    ]);

    $settings = app(LoanSettings::class);
    $settings->enable_prepayment_penalty = true;
    $settings->prepayment_penalty_percentage = 50.0;
    $settings->save();

    // Deposit funds into general wallet
    $user->deposit(1500, WalletType::General, 'Initial deposit');

    $action = app(PayoffLoanAction::class);
    $repayment = $action->handle($loan, $user);

    expect($repayment->amount)->toEqual(1012.5);
    expect($repayment->principal_component)->toEqual(1000);
    expect($repayment->interest_component)->toEqual(5);
    expect($repayment->penalty_component)->toEqual(7.5);

    // Wallet balance check
    $user->refresh();
    expect($user->getWalletBalanceByType(WalletType::General))->toEqual(1500 - 1012.5);

    // Loan status and balance checks
    $loan->refresh();
    expect($loan->status)->toBe(LoanStatus::Completed);
    expect($loan->outstanding_balance)->toEqual(0);

    // Schedule entries checks
    foreach ($loan->scheduleEntries as $entry) {
        expect($entry->status)->toBe(LoanScheduleEntryStatus::Paid);
        expect($entry->remaining_amount)->toEqual(0);
        expect($entry->paid_at)->not->toBeNull();
    }
});
