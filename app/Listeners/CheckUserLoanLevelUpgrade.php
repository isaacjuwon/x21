<?php

namespace App\Listeners;

use App\Events\Loans\LoanPaymentMade;
use App\Models\LoanLevel;
use App\Models\LoanPayment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CheckUserLoanLevelUpgrade implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(LoanPaymentMade $event): void
    {
        $user = $event->user;
        
        // Count total successful loan payments for this user
        $totalRepayments = LoanPayment::whereHas('loan', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();

        // Get all active loan levels ordered by repayments_required_for_upgrade descending
        $qualifyingLevel = LoanLevel::active()
            ->where('repayments_required_for_upgrade', '<=', $totalRepayments)
            ->orderBy('repayments_required_for_upgrade', 'desc')
            ->first();

        // If user qualifies for a higher level, upgrade them
        if ($qualifyingLevel && (!$user->loan_level_id || $qualifyingLevel->id !== $user->loan_level_id)) {
            // Check if this is actually an upgrade (higher repayment requirement)
            $currentLevel = $user->loanLevel;
            
            if (!$currentLevel || $qualifyingLevel->repayments_required_for_upgrade > $currentLevel->repayments_required_for_upgrade) {
                $user->loan_level_id = $qualifyingLevel->id;
                $user->saveQuietly();
            }
        }
    }
}
