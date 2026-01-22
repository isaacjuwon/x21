<?php

namespace App\Actions\Loans;

use App\Concerns\CalculatesLoanEligibility;
use App\Enums\LoanStatus;
use App\Events\Loans\LoanApplied;
use App\Exceptions\Loans\ActiveLoanExistsException;
use App\Exceptions\Loans\InsufficientSharesForLoanException;
use App\Exceptions\Loans\LoanLimitExceededException;
use App\Models\Loan;
use App\Models\User;

class ApplyForLoanAction
{
    use CalculatesLoanEligibility;

    /**
     * Apply for a loan
     *
     * @throws ActiveLoanExistsException
     * @throws InsufficientSharesForLoanException
     * @throws LoanLimitExceededException
     */
    public function execute(User $user, float $amount): Loan
    {
        // Check if user has an active loan
        if ($user->hasActiveLoan()) {
            throw new ActiveLoanExistsException;
        }

        // Check if user is fully KYC verified (BVN & NIN)
        if (! $user->isVerified()) {
            throw new \Exception('You must complete both BVN and NIN verification before applying for a loan.');
        }

        // Check if user has a loan level assigned
        if (! $user->loan_level_id) {
            throw new LoanLimitExceededException(
                'You must have a loan level assigned before applying.',
            );
        }

        // Get user's loan level
        $loanLevel = $user->loanLevel;

        // Calculate eligibility
        $sharesValue = $user->getSharesValue();
        $eligibleAmount = $this->calculateEligibleAmount(
            $sharesValue,
            $loanLevel->maximum_loan_amount,
        );

        // Check if user has required shares
        if (! $user->hasRequiredShares() || $eligibleAmount <= 0) {
            throw new InsufficientSharesForLoanException;
        }

        // Check if requested amount is within eligible amount
        if ($amount > $eligibleAmount) {
            throw new LoanLimitExceededException(
                'Requested amount ($'.
                    number_format($amount, 2).
                    ') exceeds your eligible amount ($'.
                    number_format($eligibleAmount, 2).
                    ').',
            );
        }

        // Check if amount is within loan level maximum
        if ($amount > $loanLevel->maximum_loan_amount) {
            throw new LoanLimitExceededException(
                'Requested amount exceeds your loan level maximum of $'.
                    number_format($loanLevel->maximum_loan_amount, 2).
                    '.',
            );
        }

        // Calculate loan details
        $monthlyPayment = $this->calculateMonthlyPayment(
            $amount,
            $loanLevel->interest_rate,
            $loanLevel->installment_period_months,
        );

        $totalRepayment = $this->calculateTotalRepayment(
            $amount,
            $loanLevel->interest_rate,
            $loanLevel->installment_period_months,
        );

        $requiredShares = $this->calculateRequiredShares($amount);

        // Create loan
        $loan = Loan::create([
            'user_id' => $user->id,
            'loan_level_id' => $loanLevel->id,
            'amount' => $amount,
            'interest_rate' => $loanLevel->interest_rate,
            'installment_months' => $loanLevel->installment_period_months,
            'monthly_payment' => $monthlyPayment,
            'total_repayment' => $totalRepayment,
            'amount_paid' => 0,
            'balance_remaining' => $totalRepayment,
            'shares_required' => $requiredShares,
            'shares_value_at_application' => $sharesValue,
            'status' => LoanStatus::Pending,
            'applied_at' => now(),
        ]);

        // Dispatch event
        event(new LoanApplied($loan, $user));

        return $loan;
    }
}
