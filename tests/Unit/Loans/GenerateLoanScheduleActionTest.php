<?php

use App\Actions\Loans\GenerateLoanScheduleAction;
use App\Enums\Loans\InterestMethod;
use App\Enums\Loans\LoanScheduleEntryStatus;
use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('flat rate schedule generates correct number of entries', function () {
    $loan = Loan::factory()->disbursed()->create([
        'principal_amount' => 10000,
        'outstanding_balance' => 10000,
        'interest_rate' => 0.15,
        'repayment_term_months' => 12,
        'interest_method' => InterestMethod::FlatRate,
    ]);

    (new GenerateLoanScheduleAction())->handle($loan);

    expect($loan->scheduleEntries()->count())->toBe(12);
});

test('flat rate schedule sum of instalments equals principal plus total interest', function () {
    $principal = 10000;
    $rate = 0.15;
    $term = 12;

    $loan = Loan::factory()->disbursed()->create([
        'principal_amount' => $principal,
        'outstanding_balance' => $principal,
        'interest_rate' => $rate,
        'repayment_term_months' => $term,
        'interest_method' => InterestMethod::FlatRate,
    ]);

    (new GenerateLoanScheduleAction())->handle($loan);

    $totalInterest = $principal * $rate * ($term / 12);
    $expectedTotal = $principal + $totalInterest;
    $actualTotal = $loan->scheduleEntries()->sum('instalment_amount');

    expect(abs($actualTotal - $expectedTotal))->toBeLessThan(1.0);
});

test('flat rate schedule entries all have pending status', function () {
    $loan = Loan::factory()->disbursed()->create([
        'principal_amount' => 5000,
        'outstanding_balance' => 5000,
        'interest_rate' => 0.12,
        'repayment_term_months' => 6,
        'interest_method' => InterestMethod::FlatRate,
    ]);

    (new GenerateLoanScheduleAction())->handle($loan);

    $entries = $loan->scheduleEntries()->get();
    foreach ($entries as $entry) {
        expect($entry->status)->toBe(LoanScheduleEntryStatus::Pending);
        expect($entry->remaining_amount)->toEqual($entry->instalment_amount);
    }
});

test('reducing balance schedule generates correct number of entries', function () {
    $loan = Loan::factory()->disbursed()->create([
        'principal_amount' => 10000,
        'outstanding_balance' => 10000,
        'interest_rate' => 0.12,
        'repayment_term_months' => 12,
        'interest_method' => InterestMethod::ReducingBalance,
    ]);

    (new GenerateLoanScheduleAction())->handle($loan);

    expect($loan->scheduleEntries()->count())->toBe(12);
});

test('reducing balance schedule final outstanding balance is approximately zero', function () {
    $loan = Loan::factory()->disbursed()->create([
        'principal_amount' => 10000,
        'outstanding_balance' => 10000,
        'interest_rate' => 0.12,
        'repayment_term_months' => 12,
        'interest_method' => InterestMethod::ReducingBalance,
    ]);

    (new GenerateLoanScheduleAction())->handle($loan);

    $lastEntry = $loan->scheduleEntries()->orderBy('instalment_number', 'desc')->first();

    expect(abs((float) $lastEntry->outstanding_balance))->toBeLessThan(1.0);
});
