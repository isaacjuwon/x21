<?php

namespace App\Actions\Loans;

use App\Enums\Loans\LoanScheduleEntryStatus;
use App\Models\Loan;
use App\Settings\LoanSettings;
use Illuminate\Support\Carbon;

class CalculateLoanPayoffAction
{
    public function __construct(
        private readonly LoanSettings $settings
    ) {}

    /**
     * Calculate early payoff quote.
     *
     * @return array{remaining_principal: float, accrued_interest: float, prepayment_penalty: float, total_payoff_amount: float}
     */
    public function handle(Loan $loan): array
    {
        // Get all schedule entries for this loan, sorted by due date
        $allEntries = $loan->scheduleEntries()
            ->orderBy('due_date')
            ->get();

        $remainingPrincipal = 0.0;
        $accruedInterest = 0.0;
        $remainingFutureInterest = 0.0;

        $now = now();

        foreach ($allEntries as $index => $entry) {
            // Only care about unpaid entries (where status is not paid or remaining_amount > 0)
            if ($entry->status === LoanScheduleEntryStatus::Paid || (float) $entry->remaining_amount <= 0) {
                continue;
            }

            // Determine start date of this installment's billing period
            if ($index === 0) {
                $start = $loan->disbursed_at ? Carbon::parse($loan->disbursed_at) : Carbon::parse($loan->created_at);
            } else {
                $start = Carbon::parse($allEntries[$index - 1]->due_date);
            }

            $due = Carbon::parse($entry->due_date);

            // Calculate the remaining principal and remaining interest components of this entry
            $installmentAmount = (float) $entry->instalment_amount;
            $remainingAmount = (float) $entry->remaining_amount;

            $principalComponent = (float) $entry->principal_component;
            $interestComponent = (float) $entry->interest_component;

            if ($installmentAmount > 0) {
                $ratio = $remainingAmount / $installmentAmount;
                $remPrincipal = round($principalComponent * $ratio, 2);
                $remInterest = round($interestComponent * $ratio, 2);
            } else {
                $remPrincipal = 0.0;
                $remInterest = 0.0;
            }

            $remainingPrincipal += $remPrincipal;

            if ($now->greaterThan($due)) {
                // Past unpaid: 100% accrued interest
                $accruedInterest += $remInterest;
            } elseif ($now->lessThan($start)) {
                // Future unpaid: 0% accrued interest, 100% future interest
                $remainingFutureInterest += $remInterest;
            } else {
                // Current active installment: pro-rata calculation
                $totalDays = $start->diffInDays($due);
                $daysElapsed = $start->diffInDays($now);

                if ($totalDays > 0) {
                    $daysRatio = min(1.0, max(0.0, $daysElapsed / $totalDays));
                } else {
                    $daysRatio = 1.0;
                }

                $accrued = round($remInterest * $daysRatio, 2);
                $unaccrued = round($remInterest * (1 - $daysRatio), 2);

                $accruedInterest += $accrued;
                $remainingFutureInterest += $unaccrued;
            }
        }

        $prepaymentPenalty = 0.0;
        if ($this->settings->enable_prepayment_penalty) {
            $penaltyPercentage = (float) $this->settings->prepayment_penalty_percentage;
            $prepaymentPenalty = round($remainingFutureInterest * ($penaltyPercentage / 100), 2);
        }

        $totalPayoffAmount = round($remainingPrincipal + $accruedInterest + $prepaymentPenalty, 2);

        return [
            'remaining_principal' => round($remainingPrincipal, 2),
            'accrued_interest' => round($accruedInterest, 2),
            'prepayment_penalty' => round($prepaymentPenalty, 2),
            'total_payoff_amount' => $totalPayoffAmount,
        ];
    }
}
