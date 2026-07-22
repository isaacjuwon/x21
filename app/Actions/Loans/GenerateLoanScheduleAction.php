<?php

namespace App\Actions\Loans;

use App\Enums\Loans\InterestMethod;
use App\Enums\Loans\LoanScheduleEntryStatus;
use App\Models\Loan;
use App\Models\LoanScheduleEntry;
use Illuminate\Support\Carbon;

class GenerateLoanScheduleAction
{
    public function handle(Loan $loan): void
    {
        // Delete existing entries before regenerating (idempotent)
        $loan->scheduleEntries()->delete();

        $entries = match ($loan->interest_method) {
            InterestMethod::FlatRate => $this->buildFlatRateSchedule($loan),
            InterestMethod::ReducingBalance => $this->buildReducingBalanceSchedule($loan),
        };

        LoanScheduleEntry::insert($entries);

        $totalOutstanding = collect($entries)->sum('instalment_amount');
        $loan->update(['outstanding_balance' => $totalOutstanding]);
    }

    /**
     * Use disbursed_at if available, otherwise use now() as the start date.
     */
    private function startDate(Loan $loan): Carbon
    {
        return $loan->disbursed_at ? Carbon::parse($loan->disbursed_at) : Carbon::now();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildFlatRateSchedule(Loan $loan): array
    {
        $principal = (float) $loan->principal_amount;
        $rate = (float) $loan->interest_rate; // Stored as decimal(5,4) e.g. 0.2500
        $term = (int) $loan->repayment_term_months;
        $start = $this->startDate($loan);

        $interestComponent = round($principal * $rate, 2);
        $principalComponent = round($principal / $term, 2);
        $instalmentAmount = $principalComponent + $interestComponent;

        $now = Carbon::now();
        $entries = [];

        for ($n = 1; $n <= $term; $n++) {
            $outstandingBalance = round($principal - ($principalComponent * $n), 2);

            $entries[] = [
                'loan_id' => $loan->id,
                'instalment_number' => $n,
                'due_date' => $start->copy()->addMonths($n)->toDateString(),
                'instalment_amount' => $instalmentAmount,
                'principal_component' => $principalComponent,
                'interest_component' => $interestComponent,
                'outstanding_balance' => max(0, $outstandingBalance),
                'status' => LoanScheduleEntryStatus::Pending->value,
                'remaining_amount' => $instalmentAmount,
                'paid_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $entries;
    }

    private function buildReducingBalanceSchedule(Loan $loan): array
    {
        $principal = (float) $loan->principal_amount;
        $rate = (float) $loan->interest_rate;
        $term = (int) $loan->repayment_term_months;
        $start = $this->startDate($loan);

        $principalComponent = round($principal / $term, 2);

        $now = Carbon::now();
        $entries = [];
        $outstandingBalance = $principal;

        for ($n = 1; $n <= $term; $n++) {
            $interestComponent = round($outstandingBalance * $rate, 2);
            $instalmentAmount = $principalComponent + $interestComponent;
            
            $nextOutstandingBalance = round($principal - ($principalComponent * $n), 2);

            $entries[] = [
                'loan_id' => $loan->id,
                'instalment_number' => $n,
                'due_date' => $start->copy()->addMonths($n)->toDateString(),
                'instalment_amount' => $instalmentAmount,
                'principal_component' => $principalComponent,
                'interest_component' => $interestComponent,
                'outstanding_balance' => max(0, $nextOutstandingBalance),
                'status' => LoanScheduleEntryStatus::Pending->value,
                'remaining_amount' => $instalmentAmount,
                'paid_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $outstandingBalance = max(0, $nextOutstandingBalance);
        }

        return $entries;
    }
}
