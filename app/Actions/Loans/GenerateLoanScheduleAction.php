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
        $entries = match ($loan->interest_method) {
            InterestMethod::FlatRate => $this->buildFlatRateSchedule($loan),
            InterestMethod::ReducingBalance => $this->buildReducingBalanceSchedule($loan),
        };

        LoanScheduleEntry::insert($entries);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildFlatRateSchedule(Loan $loan): array
    {
        $principal = (float) $loan->principal_amount;
        $rate = (float) $loan->interest_rate;
        $term = (int) $loan->repayment_term_months;

        $totalInterest = $principal * $rate * ($term / 12);
        $instalmentAmount = round(($principal + $totalInterest) / $term, 2);
        $interestComponent = round($totalInterest / $term, 2);
        $principalComponent = round($principal / $term, 2);

        $now = Carbon::now();
        $entries = [];

        for ($n = 1; $n <= $term; $n++) {
            $outstandingBalance = round($principal - ($principalComponent * $n), 2);

            $entries[] = [
                'loan_id' => $loan->id,
                'instalment_number' => $n,
                'due_date' => $loan->disbursed_at->addMonths($n)->toDateString(),
                'instalment_amount' => $instalmentAmount,
                'principal_component' => $principalComponent,
                'interest_component' => $interestComponent,
                'outstanding_balance' => $outstandingBalance,
                'status' => LoanScheduleEntryStatus::Pending->value,
                'remaining_amount' => $instalmentAmount,
                'paid_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $entries;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildReducingBalanceSchedule(Loan $loan): array
    {
        $principal = (float) $loan->principal_amount;
        $annualRate = (float) $loan->interest_rate;
        $term = (int) $loan->repayment_term_months;

        $monthlyRate = $annualRate / 12;
        $instalmentAmount = round(
            $principal * $monthlyRate / (1 - (1 + $monthlyRate) ** (-$term)),
            2
        );

        $now = Carbon::now();
        $entries = [];
        $outstandingBalance = $principal;

        for ($n = 1; $n <= $term; $n++) {
            $interestComponent = round($outstandingBalance * $monthlyRate, 2);
            $principalComponent = round($instalmentAmount - $interestComponent, 2);
            $outstandingBalance = round($outstandingBalance - $principalComponent, 2);

            $entries[] = [
                'loan_id' => $loan->id,
                'instalment_number' => $n,
                'due_date' => $loan->disbursed_at->addMonths($n)->toDateString(),
                'instalment_amount' => $instalmentAmount,
                'principal_component' => $principalComponent,
                'interest_component' => $interestComponent,
                'outstanding_balance' => $outstandingBalance,
                'status' => LoanScheduleEntryStatus::Pending->value,
                'remaining_amount' => $instalmentAmount,
                'paid_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $entries;
    }
}
