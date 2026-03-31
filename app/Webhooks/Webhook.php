<?php

namespace App\Webhooks;

use App\Enums\Webhooks\WebhookStatus;
use App\Events\Webhooks\WebhookReceived;
use App\Jobs\ProcessWebhookJob;
use App\Models\WebhookLog;
use App\Webhooks\Contracts\WebhookProcessorContract;
use App\Webhooks\Contracts\WebhookVerifierContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
     * Defaults to provider + event + reference from payload.
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
            /** @var WebhookVerifierContract $verifier */
            $verifier = app($this->verifierClass);

            if (! $verifier->verify($this->request)) {
                Log::warning("Webhook signature verification failed [{$this->provider}]");

                return response()->json(['message' => 'Unauthorized'], 401);
            }
        }

        // 2. Resolve idempotency key
        $idempotencyKey = $this->resolveIdempotencyKey();

        // 3. Deduplicate — if we've already seen this exact webhook, ignore it
        if ($idempotencyKey) {
            $existing = WebhookLog::where('idempotency_key', $idempotencyKey)
                ->whereIn('status', [WebhookStatus::Processed, WebhookStatus::Processing])
                ->first();

            if ($existing) {
                return response()->json(['message' => 'Already processed'], 200);
            }
        }

        // 4. Persist the incoming webhook
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

        WebhookReceived::dispatch($log);

        // 5. Dispatch processor
        if (! $this->processorClass) {
            $log->markIgnored('No processor configured');

            return response()->json(['message' => 'Received'], 200);
        }

        if ($this->queued) {
            ProcessWebhookJob::dispatch($log, $this->processorClass)
                ->onQueue($this->queue);
        } else {
            ProcessWebhookJob::dispatchSync($log, $this->processorClass);
        }

        // Always return 200 immediately — processing happens async
        return response()->json(['message' => 'Received'], 200);
    }

    /**
     * Build an idempotency key from provider + event + reference.
     */
    private function resolveIdempotencyKey(): ?string
    {
        if ($this->idempotencyKey) {
            return $this->idempotencyKey;
        }

        $event = $this->extractEventType();
        $reference = $this->extractReference();

        if ($event && $reference) {
            return hash('sha256', "{$this->provider}:{$event}:{$reference}");
        }

        return null;
    }

    /**
     * Try to extract the event type from common payload shapes.
     */
    private function extractEventType(): ?string
    {
        $payload = $this->request->all();

        return $payload['event'] ?? $payload['event_type'] ?? $payload['type'] ?? null;
    }

    /**
     * Try to extract a reference from common payload shapes.
     */
    private function extractReference(): ?string
    {
        $payload = $this->request->all();

        return $payload['data']['reference'] ?? $payload['ref'] ?? $payload['reference'] ?? null;
    }
}
