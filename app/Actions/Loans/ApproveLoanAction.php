<?php

namespace App\Actions\Loans;

use App\Enums\Loans\LoanStatus;
use App\Events\Loans\LoanApproved;
use App\Exceptions\Loans\InvalidLoanStateException;
use App\Models\Loan;
use App\Models\LoanStatusHistory;
use App\Models\User;
use App\Notifications\Loans\LoanApprovedNotification;
use App\Actions\Loans\CheckLoanEligibilityAction;

class ApproveLoanAction
{
    public function handle(Loan $loan, User $actor, ?string $notes = null): Loan
    {
        if ($loan->status !== LoanStatus::Active) {
            throw new InvalidLoanStateException('Loan must be in active status to be approved.');
        }

        $eligibility = app(CheckLoanEligibilityAction::class)->handle($loan->user, (float) $loan->principal_amount);

        if (! $eligibility->passed) {
            throw new \Exception('Borrower is no longer eligible for this loan: ' . $eligibility->failingSpecification->failureReason());
        }

        $fromStatus = $loan->status->value;

        $loan->status = LoanStatus::Approved;
        $loan->notes = $notes;
        $loan->save();

        LoanStatusHistory::create([
            'loan_id' => $loan->id,
            'from_status' => $fromStatus,
            'to_status' => LoanStatus::Approved->value,
            'actor_user_id' => $actor->id,
            'notes' => $notes,
        ]);

        LoanApproved::dispatch($loan);

        $loan->user->notify(new LoanApprovedNotification($loan));

        return $loan;
    }
}
