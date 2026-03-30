<?php

namespace App\Actions\Loans;

use App\Enums\Loans\LoanScheduleEntryStatus;
use App\Enums\Loans\LoanStatus;
use App\Enums\Wallets\WalletType;
use App\Events\Loans\LoanSettled;
use App\Exceptions\Loans\InvalidLoanStateException;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\User;
use App\Notifications\Loans\LoanSettledNotification;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class RepayLoanAction
{
    public function handle(Loan $loan, User $actor, float $amount): LoanRepayment
    {
        if ($loan->status !== LoanStatus::Disbursed) {
            throw new InvalidLoanStateException('Loan must be in disbursed status to accept repayments.');
        }

        if ($amount > (float) $loan->outstanding_balance) {
            throw new HttpResponseException(
                response()->json(['message' => 'Repayment amount exceeds outstanding balance.'], Response::HTTP_UNPROCESSABLE_ENTITY)
            );
        }

        $transaction = $loan->user->withdraw($amount, WalletType::General);

        $repayment = LoanRepayment::create([
            'loan_id' => $loan->id,
            'amount' => $amount,
            'transaction_id' => $transaction->id,
        ]);

        $loan->outstanding_balance = round((float) $loan->outstanding_balance - $amount, 2);
        $loan->save();

        // Apply payment to the earliest pending schedule entry
        $remaining = $amount;
        $pendingEntries = $loan->scheduleEntries()
            ->where('status', LoanScheduleEntryStatus::Pending)
            ->orderBy('instalment_number')
            ->get();

        foreach ($pendingEntries as $entry) {
            if ($remaining <= 0) {
                break;
            }

            $entryRemaining = (float) $entry->remaining_amount;

            if ($remaining >= $entryRemaining) {
                $remaining -= $entryRemaining;
                $entry->remaining_amount = 0;
                $entry->status = LoanScheduleEntryStatus::Paid;
                $entry->paid_at = now();
                $entry->save();
            } else {
                $entry->remaining_amount = round($entryRemaining - $remaining, 2);
                $remaining = 0;
                $entry->save();
            }
        }

        if ((float) $loan->outstanding_balance <= 0) {
            LoanSettled::dispatch($loan);
            $loan->user->notify(new LoanSettledNotification($loan));
        }

        return $repayment;
    }
}
