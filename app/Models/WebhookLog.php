<?php

namespace App\Models;

use App\Enums\Webhooks\WebhookStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
            'processed_at' => 'datetime',
            'next_retry_at' => 'datetime',
        ];
    }

    public function isProcessed(): bool
    {
        return $this->status === WebhookStatus::Processed;
    }

    public function hasFailed(): bool
    {
        return $this->status === WebhookStatus::Failed;
    }

    public function canRetry(): bool
    {
        return $this->hasFailed() && $this->attempts < $this->max_attempts;
    }

    public function markProcessing(): void
    {
        $this->update([
            'status' => WebhookStatus::Processing,
            'attempts' => $this->attempts + 1,
        ]);
    }

    public function markProcessed(): void
    {
        $this->update([
            'status' => WebhookStatus::Processed,
            'processed_at' => now(),
            'error_message' => null,
        ]);
    }

    public function markFailed(string $message): void
    {
        $retryAt = $this->canRetry()
            ? now()->addSeconds(min(60 * (2 ** $this->attempts), 3600))
            : null;

        $this->update([
            'status' => WebhookStatus::Failed,
            'error_message' => $message,
            'next_retry_at' => $retryAt,
        ]);
    }

    public function markIgnored(string $reason = 'Duplicate or unsupported event'): void
    {
        $this->update([
            'status' => WebhookStatus::Ignored,
            'error_message' => $reason,
            'processed_at' => now(),
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
}
