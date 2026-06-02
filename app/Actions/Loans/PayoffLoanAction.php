<?php

namespace App\Actions\Loans;

use App\Enums\Loans\LoanScheduleEntryStatus;
use App\Enums\Loans\LoanStatus;
use App\Enums\Wallets\WalletType;
use App\Events\Loans\LoanSettled;
use App\Exceptions\Wallets\InsufficientFundsException;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\LoanStatusHistory;
use App\Models\User;
use App\Notifications\Loans\LoanSettledNotification;
use Illuminate\Support\Facades\DB;

class PayoffLoanAction
{
    public function __construct(
        private readonly CalculateLoanPayoffAction $calculatePayoffAction
    ) {}

    public function handle(Loan $loan, User $actor): LoanRepayment
    {
        return DB::transaction(function () use ($loan, $actor) {
            $quote = $this->calculatePayoffAction->handle($loan);
            $totalPayoffAmount = $quote['total_payoff_amount'];

            $wallet = $loan->user->getWallet(WalletType::General);
            if ($wallet->available_balance < $totalPayoffAmount) {
                throw new InsufficientFundsException("Insufficient funds in General wallet. Required: {$totalPayoffAmount}, Available: {$wallet->available_balance}");
            }

            $fromStatus = $loan->status->value;

            // Perform wallet withdrawal
            $transaction = $loan->user->withdraw(
                $totalPayoffAmount,
                WalletType::General,
                "Loan early payoff #{$loan->id}",
                $loan,
                $actor->id
            );

            // Record LoanRepayment
            $repayment = LoanRepayment::create([
                'loan_id' => $loan->id,
                'amount' => $totalPayoffAmount,
                'transaction_id' => $transaction->id,
                'principal_component' => $quote['remaining_principal'],
                'interest_component' => $quote['accrued_interest'],
                'penalty_component' => $quote['prepayment_penalty'],
            ]);

            // Set all pending schedule entries to Paid with remaining_amount = 0 and paid_at = now()
            $loan->scheduleEntries()
                ->where('status', '!=', LoanScheduleEntryStatus::Paid)
                ->update([
                    'status' => LoanScheduleEntryStatus::Paid->value,
                    'remaining_amount' => 0.00,
                    'paid_at' => now(),
                ]);

            // Set loan status and outstanding balance
            $loan->outstanding_balance = 0.00;
            $loan->status = LoanStatus::Completed;
            $loan->save();

            // Record status history
            LoanStatusHistory::create([
                'loan_id' => $loan->id,
                'from_status' => $fromStatus,
                'to_status' => LoanStatus::Completed->value,
                'actor_user_id' => $actor->id,
                'notes' => 'Early loan payoff settlement',
            ]);

            // Dispatch events and notify
            LoanSettled::dispatch($loan);
            $loan->user->notify(new LoanSettledNotification($loan));

            return $repayment;
        });
    }
}
