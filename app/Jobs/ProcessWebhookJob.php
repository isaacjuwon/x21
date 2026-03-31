<?php

namespace App\Jobs;

use App\Enums\Webhooks\WebhookStatus;
use App\Events\Webhooks\WebhookFailed;
use App\Events\Webhooks\WebhookProcessed;
use App\Models\WebhookLog;
use App\Webhooks\Contracts\WebhookProcessorContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessWebhookJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public WebhookLog $log,
        public string $processorClass,
    ) {}

    public function handle(): void
    {
        // Skip if already processed (idempotency guard)
        if ($this->log->isProcessed()) {
            return;
        }

        $this->log->markProcessing();

        try {
            /** @var WebhookProcessorContract $processor */
            $processor = app($this->processorClass);
            $processor->handle($this->log->payload, $this->log);

            $this->log->markProcessed();

            WebhookProcessed::dispatch($this->log);
        } catch (Throwable $e) {
            Log::error("Webhook processing failed [{$this->log->provider}:{$this->log->event_type}]: {$e->getMessage()}", [
                'webhook_log_id' => $this->log->id,
                'exception' => $e,
            ]);

            $this->log->markFailed($e->getMessage());

            WebhookFailed::dispatch($this->log, $e->getMessage());

            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        // Final failure after all retries exhausted
        $this->log->update([
            'status' => WebhookStatus::Failed,
            'error_message' => "Exhausted after {$this->log->attempts} attempts: {$e->getMessage()}",
            'next_retry_at' => null,
        ]);

        WebhookFailed::dispatch($this->log, $e->getMessage());
    }
}
