<?php

namespace App\Actions\Loans;

use App\Enums\LoanPaymentType;
use App\Enums\LoanStatus;
use App\Events\Loans\LoanFullyPaid;
use App\Events\Loans\LoanPaymentMade;
use App\Exceptions\Loans\InsufficientBalanceException;
use App\Models\Loan;
use App\Models\LoanPayment;

class MakeLoanPaymentAction
{
    /**
     * Process a loan payment
     *
     * @throws InsufficientBalanceException
     */
    public function execute(Loan $loan, float $amount): LoanPayment
    {
        $user = $loan->user;

        // Check if loan is active
        if ($loan->status !== LoanStatus::ACTIVE) {
            throw new \Exception("Can only make payments on active loans.");
        }

        // Validate payment amount
        if ($amount <= 0) {
            throw new \Exception("Payment amount must be greater than zero.");
        }

        // Check if amount exceeds balance
        if ($amount > $loan->balance_remaining) {
            $amount = $loan->balance_remaining; // Pay exact balance if overpaying
        }

        // Check wallet balance using ManagesWallet trait method
        if (!$user->hasSufficientBalance($amount)) {
            $totalBalance = $user->getTotalBalance();
            throw new InsufficientBalanceException(
                "Insufficient wallet balance. You have $" .
                    number_format($totalBalance, 2) .
                    " but need $" .
                    number_format($amount, 2) .
                    ".",
            );
        }

        // Deduct from wallet using pay method
        $user->pay($amount, "Loan payment for Loan #{$loan->id}");

        // Calculate principal and interest portions
        $monthlyRate = $loan->interest_rate / 100 / 12;
        $interestAmount = $loan->balance_remaining * $monthlyRate;
        $principalAmount = $amount - $interestAmount;

        // If interest exceeds payment, adjust
        if ($interestAmount > $amount) {
            $interestAmount = $amount;
            $principalAmount = 0;
        }

        // For simplicity, if this is a partial or final payment, adjust accordingly
        if ($principalAmount < 0) {
            $principalAmount = 0;
        }

        // Create payment record
        $payment = LoanPayment::create([
            "loan_id" => $loan->id,
            "amount" => $amount,
            "payment_type" => LoanPaymentType::SCHEDULED,
            "payment_date" => now(),
            "due_date" => $loan->next_payment_date,
            "principal_amount" => $principalAmount,
            "interest_amount" => $interestAmount,
            "balance_after" => $loan->balance_remaining - $amount,
            "wallet_transaction_id" => null, // Can be linked if wallet returns transaction ID
        ]);

        // Update loan balance
        $loan->updateBalance($amount);

        // Update next payment date if not fully paid
        if ($loan->status === LoanStatus::ACTIVE) {
            $loan->update([
                "next_payment_date" => now()->addMonth(),
            ]);
        }

        // Dispatch events
        event(new LoanPaymentMade($loan, $payment, $user));

        if ($loan->status === LoanStatus::FULLY_PAID) {
            event(new LoanFullyPaid($loan, $user));
        }

        return $payment;
    }
}
