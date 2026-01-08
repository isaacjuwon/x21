<?php

namespace App\Actions\Loans;

use App\Concerns\CalculatesLoanEligibility;
use App\Models\Loan;
use Carbon\Carbon;

class CalculateLoanScheduleAction
{
    use CalculatesLoanEligibility;

    /**
     * Calculate complete payment schedule for a loan
     */
    public function execute(Loan $loan): array
    {
        $startDate = $loan->disbursed_at
            ? Carbon::parse($loan->disbursed_at)
            : now();

        return $this->generatePaymentSchedule(
            $loan->amount,
            $loan->interest_rate,
            $loan->installment_months,
            $startDate,
        );
    }
}
