<?php

namespace App\Actions\Webhooks;

use App\Enums\Wallets\TransactionStatus;
use App\Models\Transaction;
use App\Models\WebhookLog;
use App\Webhooks\Contracts\WebhookProcessorContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPaystackWebhookAction implements WebhookProcessorContract
{
    public function handle(array $payload, WebhookLog $log): void
    {
        $event = $payload['event'] ?? null;
        $data = $payload['data'] ?? [];

        // Update log with extracted fields
        $log->update([
            'event_type' => $event,
            'reference' => $data['reference'] ?? $log->reference,
        ]);

        match ($event) {
            'charge.success' => $this->handleChargeSuccess($data),
            'transfer.success' => $this->handleTransferSuccess($data),
            'transfer.failed' => $this->handleTransferFailed($data),
            'transfer.reversed' => $this->handleTransferReversed($data),
            default => Log::info("Unhandled Paystack event: {$event}", ['log_id' => $log->id]),
        };
    }

    private function handleChargeSuccess(array $data): void
    {
        $reference = $data['reference'] ?? null;

        if (! $reference) {
            return;
        }

        $transaction = Transaction::where('reference', $reference)
            ->where('status', TransactionStatus::Pending)  // Only act on pending — idempotent
            ->first();

        if (! $transaction) {
            Log::info("Paystack charge.success: Transaction {$reference} already processed or not found — skipping.");

            return;
        }

        DB::transaction(function () use ($transaction, $reference) {
            // Re-check inside the DB transaction to prevent race with callback verify
            $transaction->refresh();

            if ($transaction->status !== TransactionStatus::Pending) {
                return;
            }

            $transaction->update([
                'status' => TransactionStatus::Completed,
                'meta' => array_merge($transaction->meta ?? [], ['credited_by' => 'webhook']),
            ]);

            $transaction->wallet->increment('balance', $transaction->amount);

            Log::info("Paystack charge.success: Transaction {$reference} credited via webhook.");
        });
    }

    private function handleTransferSuccess(array $data): void
    {
        $reference = $data['reference'] ?? null;

        if (! $reference) {
            return;
        }

        $transaction = Transaction::where('reference', $reference)
            ->where('status', TransactionStatus::Pending)
            ->first();

        if ($transaction) {
            $transaction->update(['status' => TransactionStatus::Completed]);
            Log::info("Paystack transfer.success: Transaction {$reference} completed.");
        }
    }

    private function handleTransferFailed(array $data): void
    {
        $reference = $data['reference'] ?? null;

        if (! $reference) {
            return;
        }

        $transaction = Transaction::where('reference', $reference)
            ->where('status', TransactionStatus::Pending)
            ->first();

        if ($transaction) {
            // Reverse the wallet deduction
            $transaction->wallet->increment('balance', $transaction->amount);
            $transaction->update(['status' => TransactionStatus::Voided]);
            Log::info("Paystack transfer.failed: Transaction {$reference} reversed.");
        }
    }

    private function handleTransferReversed(array $data): void
    {
        $this->handleTransferFailed($data);
    }
}
