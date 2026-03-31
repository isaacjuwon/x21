<?php

namespace App\Actions\Wallets;

use App\Enums\Wallets\TransactionStatus;
use App\Integrations\Paystack\PaystackConnector;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class VerifyWalletFundingAction
{
    public function __construct(
        protected PaystackConnector $paystack,
    ) {}

    /**
     * Verify a Paystack callback and credit the wallet if not already done.
     *
     * Returns the transaction with its final status.
     */
    public function handle(string $reference): Transaction
    {
        $transaction = Transaction::where('reference', $reference)->firstOrFail();

        // Already processed (webhook beat us to it) — just return
        if ($transaction->status === TransactionStatus::Completed) {
            return $transaction;
        }

        // Verify with Paystack API
        $response = $this->paystack->transactions()->verify($reference);

        if ($response->status !== 'success') {
            $transaction->update(['status' => TransactionStatus::Voided]);

            return $transaction;
        }

        // Guard: amount must match
        if (round((float) $transaction->amount, 2) !== round($response->amount, 2)) {
            $transaction->update([
                'status' => TransactionStatus::Voided,
                'notes' => 'Amount mismatch — expected '.$transaction->amount.', got '.$response->amount,
            ]);

            return $transaction;
        }

        // Credit wallet — idempotent: only if still pending
        DB::transaction(function () use ($transaction, $response) {
            $transaction->refresh();

            if ($transaction->status !== TransactionStatus::Pending) {
                return; // Webhook already handled it
            }

            $transaction->update([
                'status' => TransactionStatus::Completed,
                'meta' => array_merge($transaction->meta ?? [], [
                    'paystack_verified_at' => now()->toISOString(),
                    'paystack_data' => $response->metadata,
                ]),
            ]);

            $transaction->wallet->increment('balance', $transaction->amount);
        });

        return $transaction->fresh();
    }
}
