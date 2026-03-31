<?php

namespace App\Actions\Wallets;

use App\Enums\Wallets\TransactionStatus;
use App\Enums\Wallets\TransactionType;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReverseWalletTransactionAction
{
    /**
     * Reverse a failed withdrawal by crediting the wallet back.
     *
     * Idempotent — safe to call multiple times; will not double-refund.
     */
    public function handle(Transaction $transaction, string $reason): Transaction
    {
        // Guard: only reverse completed withdrawals that haven't been refunded yet
        if ($transaction->status !== TransactionStatus::Failed) {
            throw new \LogicException("Cannot reverse transaction #{$transaction->id}: status is {$transaction->status->value}.");
        }

        if ($transaction->refund()->exists()) {
            return $transaction->refund;
        }

        return DB::transaction(function () use ($transaction, $reason) {
            // Re-check inside DB transaction to prevent race conditions
            $transaction->refresh();

            if ($transaction->refund()->exists()) {
                return $transaction->refund;
            }

            // Credit the wallet back
            $transaction->wallet->increment('balance', $transaction->amount);

            // Create the refund transaction record
            $refund = $transaction->wallet->transactions()->create([
                'amount' => $transaction->amount,
                'type' => TransactionType::Deposit,
                'status' => TransactionStatus::Completed,
                'reference' => 'RFD-'.strtoupper(Str::random(10)),
                'notes' => "Refund for failed transaction #{$transaction->reference}: {$reason}",
                'refund_for_id' => $transaction->id,
                'transactionable_id' => $transaction->transactionable_id,
                'transactionable_type' => $transaction->transactionable_type,
            ]);

            // Mark the original as refunded
            $transaction->update(['status' => TransactionStatus::Refunded]);

            return $refund;
        });
    }
}
