<?php

namespace App\Actions\Loans;

use App\Enums\LoanStatus;
use App\Enums\WalletType;
use App\Events\Loans\LoanDisbursed;
use App\Models\Loan;

class DisburseLoanAction
{
    /**
     * Disburse approved loan to user's wallet
     */
    public function execute(Loan $loan): void
    {
        // Only disburse approved loans
        if ($loan->status !== LoanStatus::APPROVED) {
            throw new \Exception("Only approved loans can be disbursed.");
        }

        $user = $loan->user;

        // Deposit to user's main wallet
        $user->deposit(WalletType::MAIN, $loan->amount, "Loan disbursement for Loan #{$loan->id}");

        // Update loan status
        $loan->update([
            "status" => LoanStatus::ACTIVE,
            "disbursed_at" => now(),
        ]);

        // Sync next payment date correctly based on disbursement
        $loan->syncNextPaymentDate();
        $loan->save();

        // Dispatch event
        event(new LoanDisbursed($loan, $user, $loan->amount));
    }
}
