<?php

namespace App\Actions\Loans;

use App\Enums\Loans\LoanStatus;
use App\Events\Loans\LoanRejected;
use App\Exceptions\Loans\InvalidLoanStateException;
use App\Models\Loan;
use App\Models\LoanStatusHistory;
use App\Models\User;
use App\Notifications\Loans\LoanRejectedNotification;

class RejectLoanAction
{
    public function handle(Loan $loan, User $actor, string $rejectionReason, ?string $notes = null): Loan
    {
        if ($loan->status === LoanStatus::Disbursed || $loan->status === LoanStatus::Rejected) {
            throw new InvalidLoanStateException('Loan cannot be rejected in its current status.');
        }

        $fromStatus = $loan->status->value;

        $loan->status = LoanStatus::Rejected;
        $loan->rejection_reason = $rejectionReason;
        $loan->notes = $notes;
        $loan->save();

        LoanStatusHistory::create([
            'loan_id' => $loan->id,
            'from_status' => $fromStatus,
            'to_status' => LoanStatus::Rejected->value,
            'actor_user_id' => $actor->id,
            'notes' => $notes,
        ]);

        LoanRejected::dispatch($loan);

        $loan->user->notify(new LoanRejectedNotification($loan));

        return $loan;
    }
}
