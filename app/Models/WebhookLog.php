<?php

namespace App\Models;

use App\Enums\Webhooks\WebhookStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookLog extends Model
{
    protected $fillable = [
        'provider',
        'event_type',
        'reference',
        'idempotency_key',
        'payload',
        'headers',
        'status',
        'attempts',
        'max_attempts',
        'next_retry_at',
        'error_message',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'headers' => 'array',
            'status' => WebhookStatus::class,
            'attempts' => 'integer',
            'max_attempts' => 'integer',
            'processed_at' => 'datetime',
            'next_retry_at' => 'datetime',
        ];
    }

    public function isProcessed(): bool
    {
        return $this->status === WebhookStatus::Processed;
    }

    public function isProcessing(): bool
    {
        return $this->status === WebhookStatus::Processing;
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [
            WebhookStatus::Processed,
            WebhookStatus::Ignored,
        ], true);
    }

    public function hasFailed(): bool
    {
        return $this->status === WebhookStatus::Failed;
    }

    public function canRetry(): bool
    {
        return $this->hasFailed() && $this->attempts < $this->max_attempts;
    }

    /**
     * Atomically transition from Pending/Failed → Processing using a
     * case-update so two concurrent workers cannot both claim the log.
     *
     * Returns true if this worker won the transition; false if another
     * worker already claimed the row.
     */
    public function tryMarkProcessing(): bool
    {
        $allowedFrom = [WebhookStatus::Pending->value, WebhookStatus::Failed->value];

        $updated = DB::table($this->getTable())
            ->where('id', $this->id)
            ->whereIn('status', $allowedFrom)
            ->update([
                'status' => WebhookStatus::Processing->value,
                'attempts' => DB::raw('attempts + 1'),
                'updated_at' => now(),
            ]);

        if ($updated === 0) {
            $this->refresh();
            Log::info("WebhookLog [{$this->id}] could not transition to Processing — claimed by another worker", [
                'provider' => $this->provider,
                'event_type' => $this->event_type,
                'current_status' => $this->status->value,
                'attempts' => $this->attempts,
            ]);

            return false;
        }

        $this->status = WebhookStatus::Processing;
        $this->attempts += 1;
        $this->syncOriginalAttribute('status');
        $this->syncOriginalAttribute('attempts');

        return true;
    }

    public function markProcessed(): void
    {
        $this->update([
            'status' => WebhookStatus::Processed,
            'processed_at' => now(),
            'error_message' => null,
            'next_retry_at' => null,
        ]);

        Log::info("WebhookLog [{$this->id}] marked Processed", [
            'provider' => $this->provider,
            'event_type' => $this->event_type,
            'attempts' => $this->attempts,
        ]);
    }

    public function markFailed(string $message): void
    {
        $retryAt = $this->canRetry()
            ? now()->addSeconds(min(60 * (2 ** ($this->attempts - 1)), 3600))
            : null;

        $this->update([
            'status' => WebhookStatus::Failed,
            'error_message' => $message,
            'next_retry_at' => $retryAt,
        ]);

        Log::warning("WebhookLog [{$this->id}] marked Failed", [
            'provider' => $this->provider,
            'event_type' => $this->event_type,
            'attempts' => $this->attempts,
            'max_attempts' => $this->max_attempts,
            'can_retry' => $this->canRetry(),
            'error' => mb_substr($message, 0, 200),
        ]);
    }

    public function markIgnored(string $reason = 'Duplicate or unsupported event'): void
    {
        $this->update([
            'status' => WebhookStatus::Ignored,
            'error_message' => $reason,
            'processed_at' => now(),
            'next_retry_at' => null,
        ]);

        Log::info("WebhookLog [{$this->id}] marked Ignored", [
            'provider' => $this->provider,
            'event_type' => $this->event_type,
            'reason' => $reason,
        ]);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', WebhookStatus::Pending);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', WebhookStatus::Failed);
    }

    public function scopeRetryable(Builder $query): Builder
    {
        return $query->where('status', WebhookStatus::Failed)
            ->whereColumn('attempts', '<', 'max_attempts')
            ->where(fn ($q) => $q->whereNull('next_retry_at')->orWhere('next_retry_at', '<=', now()));
    }

    public function scopeForProvider(Builder $query, string $provider): Builder
    {
        return $query->where('provider', $provider);
    }

    public function scopeByIdempotencyKey(Builder $query, ?string $key): Builder
    {
        return $query->whereNotNull('idempotency_key')
            ->where('idempotency_key', $key);
    }
}
