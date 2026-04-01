<?php

namespace App\Actions\Loans;

use App\Enums\Loans\LoanStatus;
use App\Enums\Wallets\WalletType;
use App\Events\Loans\LoanDisbursed;
use App\Exceptions\Loans\InvalidLoanStateException;
use App\Jobs\GenerateLoanScheduleJob;
use App\Models\Loan;
use App\Models\LoanStatusHistory;
use App\Models\User;
use App\Notifications\Loans\LoanDisbursedNotification;

class DisburseLoanAction
{
    public function handle(Loan $loan, User $actor, ?string $notes = null): Loan
    {
        if ($loan->status !== LoanStatus::Approved) {
            throw new InvalidLoanStateException('Loan must be in approved status to be disbursed.');
        }

        $fromStatus = $loan->status->value;

        $loan->user->deposit((float) $loan->principal_amount, WalletType::General, "Loan disbursement #{$loan->id}", $loan);

        $loan->status = LoanStatus::Disbursed;
        $loan->disbursed_at = now();
        $loan->notes = $notes;
        $loan->save();

        LoanStatusHistory::create([
            'loan_id' => $loan->id,
            'from_status' => $fromStatus,
            'to_status' => LoanStatus::Disbursed->value,
            'actor_user_id' => $actor->id,
            'notes' => $notes,
        ]);

        LoanDisbursed::dispatch($loan);

        GenerateLoanScheduleJob::dispatch($loan);

        $loan->user->notify(new LoanDisbursedNotification($loan));

        return $loan;
    }
}
