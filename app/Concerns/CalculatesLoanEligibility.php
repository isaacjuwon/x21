<?php

namespace App\Concerns;

use Carbon\Carbon;

trait CalculatesLoanEligibility
{
    /**
     * Calculate eligible loan amount based on shares and loan level
     */
    public function calculateEligibleAmount(float $sharesValue, float $maxLoanAmount): float
    {
        $loanSettings = app(\App\Settings\LoanSettings::class);
        $eligibleBasedOnShares = $sharesValue * $loanSettings->loan_to_shares_ratio;
        return min($eligibleBasedOnShares, $maxLoanAmount);
    }

    /**
     * Calculate required shares for a specific loan amount
     */
    public function calculateRequiredShares(float $loanAmount): float
    {
        $loanSettings = app(\App\Settings\LoanSettings::class);
        return $loanAmount / $loanSettings->loan_to_shares_ratio;
    }

    /**
     * Calculate monthly payment using amortization formula
     * P = L[c(1 + c)^n]/[(1 + c)^n - 1]
     * where:
     * P = monthly payment
     * L = loan amount
     * c = monthly interest rate (annual rate / 12)
     * n = number of payments (months)
     */
    public function calculateMonthlyPayment(
        float $loanAmount,
        float $monthlyInterestRate,
        int $months,
    ): float {
        if ($months <= 0) {
            return 0;
        }

        // If no interest, simple division
        if ($monthlyInterestRate == 0) {
            return $loanAmount / $months;
        }

        $monthlyRate = $monthlyInterestRate / 100;
        $factor = pow(1 + $monthlyRate, $months);

        $monthlyPayment =
            ($loanAmount * $monthlyRate * $factor) / ($factor - 1);

        return round($monthlyPayment, 2);
    }

    /**
     * Generate complete payment schedule
     */
    public function generatePaymentSchedule(
        float $loanAmount,
        float $monthlyInterestRate,
        int $months,
        Carbon $startDate = null,
    ): array {
        $startDate = $startDate ?? now();
        $monthlyPayment = $this->calculateMonthlyPayment(
            $loanAmount,
            $monthlyInterestRate,
            $months,
        );
        $monthlyRate = $monthlyInterestRate / 100;

        $schedule = [];
        $balance = $loanAmount;

        for ($i = 1; $i <= $months; $i++) {
            $interestAmount = $balance * $monthlyRate;
            $principalAmount = $monthlyPayment - $interestAmount;

            // Last payment adjustment to handle rounding
            if ($i == $months) {
                $principalAmount = $balance;
                $monthlyPayment = $principalAmount + $interestAmount;
            }

            $balance -= $principalAmount;

            $schedule[] = [
                "payment_number" => $i,
                "due_date" => $startDate->copy()->addMonths($i)->format("Y-m-d"),
                "payment_amount" => round($monthlyPayment, 2),
                "principal_amount" => round($principalAmount, 2),
                "interest_amount" => round($interestAmount, 2),
                "balance_remaining" => round(max(0, $balance), 2),
            ];
        }

        return $schedule;
    }

    /**
     * Calculate total interest to be paid
     */
    public function calculateTotalInterest(
        float $loanAmount,
        float $monthlyInterestRate,
        int $months,
    ): float {
        $monthlyPayment = $this->calculateMonthlyPayment(
            $loanAmount,
            $monthlyInterestRate,
            $months,
        );
        $totalRepayment = $monthlyPayment * $months;
        return round($totalRepayment - $loanAmount, 2);
    }

    /**
     * Calculate total repayment amount
     */
    public function calculateTotalRepayment(
        float $loanAmount,
        float $monthlyInterestRate,
        int $months,
    ): float {
        $monthlyPayment = $this->calculateMonthlyPayment(
            $loanAmount,
            $monthlyInterestRate,
            $months,
        );
        return round($monthlyPayment * $months, 2);
    }
}
