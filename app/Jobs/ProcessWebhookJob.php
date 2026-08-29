<?php

namespace App\Jobs;

use App\Enums\Webhooks\WebhookStatus;
use App\Events\Webhooks\WebhookFailed;
use App\Events\Webhooks\WebhookProcessed;
use App\Models\WebhookLog;
use App\Webhooks\Contracts\WebhookProcessorContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessWebhookJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * Exponential backoff: first fail → 60s, second → 120s, third → 240s.
     *
     * @var array<int, int>
     */
    public array $backoff = [60, 120, 240];

    public function __construct(
        public WebhookLog $log,
        public string $processorClass,
    ) {}

    public function handle(): void
    {
        $this->log->refresh();

        if ($this->log->isProcessed() || $this->log->isTerminal()) {
            Log::info("ProcessWebhookJob skipping [{$this->log->id}]: already in terminal state", [
                'provider' => $this->log->provider,
                'status' => $this->log->status->value,
            ]);

            return;
        }

        if (! $this->log->tryMarkProcessing()) {
            return;
        }

        try {
            /** @var WebhookProcessorContract $processor */
            $processor = app($this->processorClass);

            DB::beginTransaction();

            try {
                $processor->handle($this->log->payload, $this->log);
                DB::commit();
            } catch (Throwable $e) {
                DB::rollBack();

                throw $e;
            }

            $this->log->markProcessed();

            WebhookProcessed::dispatch($this->log);
        } catch (Throwable $e) {
            Log::error("Webhook processing failed [{$this->log->provider}:{$this->log->event_type}]: {$e->getMessage()}", [
                'webhook_log_id' => $this->log->id,
                'attempt' => $this->log->attempts,
                'exception' => $e,
            ]);

            $this->log->markFailed($e->getMessage());

            WebhookFailed::dispatch($this->log, $e->getMessage());

            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        $this->log->refresh();

        DB::table($this->log->getTable())
            ->where('id', $this->log->id)
            ->update([
                'status' => WebhookStatus::Failed->value,
                'error_message' => "Exhausted after {$this->log->attempts} attempts: {$e->getMessage()}",
                'next_retry_at' => null,
                'updated_at' => now(),
            ]);

        Log::critical("ProcessWebhookJob [{$this->log->id}] exhausted all retries", [
            'provider' => $this->log->provider,
            'event_type' => $this->log->event_type,
            'attempts' => $this->log->attempts,
            'error' => mb_substr($e->getMessage(), 0, 200),
        ]);

        WebhookFailed::dispatch($this->log->fresh(), $e->getMessage());
    }
}
