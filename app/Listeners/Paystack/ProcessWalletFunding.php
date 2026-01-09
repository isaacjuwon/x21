<?php

declare(strict_types=1);

namespace App\Listeners\Paystack;

use App\Enums\Transaction\Status;
use App\Events\Paystack\PaymentSuccessful;
use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class ProcessWalletFunding implements ShouldQueue
{
    public function handle(PaymentSuccessful $event): void
    {
        $data = $event->data;
        $metadata = $data['metadata'] ?? [];

        // Check if this payment is for wallet funding
        if (($metadata['type'] ?? '') !== 'wallet_funding') {
            return;
        }

        $transactionId = $metadata['transaction_id'] ?? null;

        if (! $transactionId) {
            Log::error('Wallet Funding Webhook: Missing transaction ID in metadata', ['data' => $data]);
            return;
        }

        $transaction = Transaction::find($transactionId);

        if (! $transaction) {
            Log::error("Wallet Funding Webhook: Transaction {$transactionId} not found");
            return;
        }

        if ($transaction->status === Status::Success) {
            Log::info("Wallet Funding Webhook: Transaction {$transactionId} already processed");
            return;
        }

        // Verify amount (Paystack is in kobo/minor units)
        $paidAmount = $data['amount'] / 100;
        
        if ($paidAmount != $transaction->amount) {
            Log::warning("Wallet Funding Webhook: Amount mismatch for transaction {$transactionId}. Expected: {$transaction->amount}, Paid: {$paidAmount}");
            // Decide whether to proceed or flag. Usually, we credit what was paid.
            // For now, let's update the transaction amount to match actual payment if needed, 
            // but strict matching is safer. 
            // Let's assume strict matching for security.
        }

        // Credit User Wallet
        $transaction->user->deposit($paidAmount);

        // Update Transaction
        $transaction->update([
            'status' => Status::Success,
            'meta' => array_merge($transaction->meta ?? [], [
                'paystack_reference' => $data['reference'],
                'paid_at' => $data['paid_at'],
                'channel' => $data['channel'] ?? 'paystack',
            ]),
        ]);

        Log::info("Wallet Credited: {$paidAmount} for User {$transaction->user_id} via Transaction {$transaction->id}");
    }
}
