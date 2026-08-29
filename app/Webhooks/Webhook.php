<?php

namespace App\Webhooks;

use App\Enums\Webhooks\WebhookStatus;
use App\Events\Webhooks\WebhookReceived;
use App\Jobs\ProcessWebhookJob;
use App\Models\WebhookLog;
use App\Webhooks\Contracts\WebhookProcessorContract;
use App\Webhooks\Contracts\WebhookVerifierContract;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class Webhook
{
    protected ?string $verifierClass = null;

    protected ?string $processorClass = null;

    protected bool $queued = true;

    protected string $queue = 'webhooks';

    protected int $maxAttempts = 3;

    protected ?string $idempotencyKey = null;

    private function __construct(
        protected string $provider,
        protected Request $request,
    ) {}

    /**
     * Start the fluent builder for an incoming webhook.
     */
    public static function receive(string $provider, Request $request): static
    {
        return new static($provider, $request);
    }

    /**
     * Set the verifier class (must implement WebhookVerifierContract).
     */
    public function verify(string $verifierClass): static
    {
        $this->verifierClass = $verifierClass;

        return $this;
    }

    /**
     * Set the processor action class (must implement WebhookProcessorContract).
     */
    public function process(string $processorClass): static
    {
        $this->processorClass = $processorClass;

        return $this;
    }

    /**
     * Process synchronously instead of queuing.
     */
    public function sync(): static
    {
        $this->queued = false;

        return $this;
    }

    /**
     * Set the queue name for async processing.
     */
    public function onQueue(string $queue): static
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * Set a custom idempotency key to deduplicate webhooks.
     * Defaults to provider + event + reference from payload, aligned
     * with whatever MapWebhookIdempotencyKey already merged into the
     * request input so cache keys and DB keys are traceable.
     */
    public function idempotencyKey(string $key): static
    {
        $this->idempotencyKey = $key;

        return $this;
    }

    /**
     * Set max retry attempts.
     */
    public function maxAttempts(int $attempts): static
    {
        $this->maxAttempts = $attempts;

        return $this;
    }

    /**
     * Execute the webhook pipeline and return an HTTP response.
     */
    public function handle(): JsonResponse
    {
        // 1. Verify signature before touching the database
        if ($this->verifierClass) {
            try {
                /** @var WebhookVerifierContract $verifier */
                $verifier = app($this->verifierClass);

                if (! $verifier->verify($this->request)) {
                    Log::warning("Webhook signature verification failed [{$this->provider}]", [
                        'ip' => $this->request->ip(),
                    ]);

                    return response()->json(['message' => 'Unauthorized'], 401);
                }
            } catch (Throwable $e) {
                Log::error("Webhook signature verifier threw for [{$this->provider}]", [
                    'error' => $e->getMessage(),
                    'exception' => $e,
                ]);

                return response()->json(['message' => 'Unauthorized'], 401);
            }
        }

        // 2. Resolve idempotency key — prefer the one MapWebhookIdempotencyKey
        //    already placed on the request so HTTP cache + DB dedup align.
        $idempotencyKey = $this->resolveIdempotencyKey();

        // 3. Persist the incoming webhook, relying on the unique index on
        //    idempotency_key for atomic deduplication rather than a
        //    SELECT-then-INSERT race window.
        try {
            $log = WebhookLog::create([
                'provider' => $this->provider,
                'event_type' => $this->extractEventType(),
                'reference' => $this->extractReference(),
                'idempotency_key' => $idempotencyKey,
                'payload' => $this->request->all(),
                'headers' => $this->request->headers->all(),
                'status' => WebhookStatus::Pending,
                'max_attempts' => $this->maxAttempts,
            ]);
        } catch (QueryException $e) {
            // Unique constraint violation on idempotency_key → duplicate.
            if ($this->isUniqueConstraintViolation($e, 'webhook_logs_idempotency_key_index')) {
                Log::info("Webhook [{$this->provider}] duplicate detected via unique constraint", [
                    'idempotency_key' => $idempotencyKey,
                    'event_type' => $this->extractEventType(),
                    'reference' => $this->extractReference(),
                ]);

                return response()->json(['message' => 'Already processed'], 200);
            }

            Log::critical("Webhook [{$this->provider}] failed to persist log record", [
                'error' => $e->getMessage(),
                'exception' => $e,
                'idempotency_key' => $idempotencyKey,
            ]);

            // ACK with 200 to avoid provider replaying into the same broken
            // state indefinitely; ops will alert from the critical log.
            return response()->json(['message' => 'Received'], 200);
        }

        try {
            WebhookReceived::dispatch($log);
        } catch (Throwable $e) {
            Log::warning("Webhook [{$this->provider}] WebhookReceived event dispatch failed", [
                'log_id' => $log->id,
                'error' => $e->getMessage(),
            ]);
        }

        // 4. Dispatch processor
        if (! $this->processorClass) {
            $log->markIgnored('No processor configured');

            return response()->json(['message' => 'Received'], 200);
        }

        try {
            if ($this->queued) {
                ProcessWebhookJob::dispatch($log, $this->processorClass)
                    ->onQueue($this->queue);
            } else {
                ProcessWebhookJob::dispatchSync($log, $this->processorClass);
            }
        } catch (Throwable $e) {
            Log::critical("Webhook [{$this->provider}] processor dispatch failed", [
                'log_id' => $log->id,
                'queued' => $this->queued,
                'queue' => $this->queue,
                'processor' => $this->processorClass,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            $log->markFailed('Processor dispatch failed: '.$e->getMessage());
        }

        return response()->json(['message' => 'Received'], 200);
    }

    /**
     * Build an idempotency key from provider + event + reference.
     * Prefer the value that MapWebhookIdempotencyKey already merged
     * into the request input so the HTTP-layer cache and the DB
     * dedup key are identical (or at least traceable).
     */
    private function resolveIdempotencyKey(): ?string
    {
        if ($this->idempotencyKey) {
            return $this->idempotencyKey;
        }

        $mergedFromMiddleware = $this->request->input(config('idempotency.input'));

        if (is_string($mergedFromMiddleware) && $mergedFromMiddleware !== '') {
            return $mergedFromMiddleware;
        }

        $event = $this->extractEventType();
        $reference = $this->extractReference();

        if ($event && $reference) {
            return sprintf('%s:%s:%s', $this->provider, $event, $reference);
        }

        return null;
    }

    /**
     * Try to extract the event type from common payload shapes.
     */
    private function extractEventType(): ?string
    {
        $payload = $this->request->all();

        $event = $payload['event'] ?? $payload['event_type'] ?? $payload['type'] ?? null;

        return is_string($event) && $event !== '' ? $event : null;
    }

    /**
     * Try to extract a reference from common payload shapes.
     */
    private function extractReference(): ?string
    {
        $payload = $this->request->all();

        $reference = $payload['data']['reference'] ?? $payload['ref'] ?? $payload['reference'] ?? null;

        return is_scalar($reference) && (string) $reference !== '' ? (string) $reference : null;
    }

    /**
     * Detect a SQL unique-constraint violation (driver-aware).
     */
    private function isUniqueConstraintViolation(QueryException $e, string $indexName): bool
    {
        $code = $e->errorInfo[1] ?? $e->getCode();
        $sqlState = $e->errorInfo[0] ?? null;

        // MySQL: 1062 duplicate entry; PostgreSQL: 23505 unique violation
        if (in_array((int) $code, [1062, 23505], true) || $sqlState === '23000' || $sqlState === '23505') {
            if ($indexName === '') {
                return true;
            }

            return stripos($e->getMessage(), $indexName) !== false
                || stripos($e->getMessage(), 'idempotency_key') !== false;
        }

        return false;
    }
}
