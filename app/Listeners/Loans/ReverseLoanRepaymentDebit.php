<?php

namespace App\Listeners\Loans;

use App\Events\Loans\LoanRepaymentFailed;
use App\Enums\WalletType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ReverseLoanRepaymentDebit
{
    /**
     * Handle the event.
     */
    public function handle(LoanRepaymentFailed $event): void
    {
        try {
            $user = $event->loan->user;
            
            // Refund the amount back to the Main wallet
            $user->deposit(
                WalletType::Main,
                $event->amount,
                "Reversal for failed loan repayment of #{$event->loan->id}. Reason: " . $event->exception->getMessage()
            );

            Log::info("Successfully reversed loan repayment debit for user #{$user->id} for loan #{$event->loan->id}");
        } catch (\Exception $e) {
            Log::error("Failed to reverse loan repayment debit for loan #{$event->loan->id}: " . $e->getMessage());
        }
    }
}
