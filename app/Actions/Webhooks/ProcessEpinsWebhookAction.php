<?php

namespace App\Actions\Webhooks;

use App\Models\WebhookLog;
use App\Webhooks\Contracts\WebhookProcessorContract;
use Illuminate\Support\Facades\Log;

class ProcessEpinsWebhookAction implements WebhookProcessorContract
{
    public function handle(array $payload, WebhookLog $log): void
    {
        $reference = $payload['ref'] ?? $payload['reference'] ?? null;
        $status = $payload['status'] ?? null;
        $event = $payload['event'] ?? 'status_change';

        $log->update([
            'event_type' => $event,
            'reference' => $reference ?? $log->reference,
        ]);

        if (! $reference) {
            Log::warning('Epins webhook: Missing reference in payload', ['log_id' => $log->id]);

            return;
        }

        // Resolve the topup transaction model dynamically to avoid hard dependency
        // on a model that may not exist yet
        $transactionModel = config('webhooks.epins.transaction_model');

        if (! $transactionModel || ! class_exists($transactionModel)) {
            Log::warning('Epins webhook: Transaction model not configured or not found.');

            return;
        }

        $transaction = $transactionModel::where('reference', $reference)->first();

        if (! $transaction) {
            Log::warning("Epins webhook: Transaction {$reference} not found.", ['log_id' => $log->id]);

            return;
        }

        match (true) {
            in_array($status, ['successful', 'completed', 'success']) => $this->markCompleted($transaction, $reference),
            in_array($status, ['failed', 'error']) => $this->markFailed($transaction, $reference),
            default => Log::info("Epins webhook: Unhandled status '{$status}' for {$reference}"),
        };
    }

    private function markCompleted(mixed $transaction, string $reference): void
    {
        $transaction->update(['status' => 'completed']);
        Log::info("Epins webhook: Topup {$reference} completed.");
    }

    private function markFailed(mixed $transaction, string $reference): void
    {
        $transaction->update(['status' => 'failed']);
        Log::info("Epins webhook: Topup {$reference} failed.");
    }
}
