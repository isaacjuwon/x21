<?php

declare(strict_types=1);

namespace App\Integrations\Failover;

use App\Http\Entities\PurchaseAirtime;
use App\Http\Entities\PurchaseCable;
use App\Http\Entities\PurchaseData;
use App\Http\Entities\PurchaseElectricity;
use App\Http\Entities\PurchaseExam;
use App\Http\Entities\ServiceResponse;
use App\Http\Entities\ValidateMeter;
use App\Http\Entities\ValidateSmartcard;
use App\Http\Entities\ValidationResponse;
use App\Integrations\Contracts\Providers\VtuProvider;
use Closure;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Model;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Throwable;

class FailoverVtuProvider implements VtuProvider
{
    /**
     * In-memory storage of dead providers: [providerName => timestamp].
     *
     * @var array<string, float>
     */
    protected array $deadProviders = [];

    /**
     * Per-execution cache of loaded plans, keyed by "planType|planId".
     * Reset at the start of each executeWithFailover call to prevent stale
     * data when this provider instance is reused across requests (e.g. queue workers).
     *
     * @var array<string, ?Model>
     */
    protected array $planCache = [];

    /**
     * @param  array<string, VtuProvider>  $providers
     * @param  int  $retryAfter  Seconds before retrying a dead provider
     */
    public function __construct(
        protected array $providers,
        protected int $retryAfter = 60,
        protected ?LoggerInterface $logger = null,
        protected ?CacheRepository $cache = null,
        protected bool $failoverOnUnsuccessful = true,
    ) {
        $this->logger ??= new NullLogger;

        if (empty($this->providers)) {
            throw new RuntimeException('FailoverVtuProvider requires at least one VTU provider.');
        }
    }

    public function purchaseAirtime(PurchaseAirtime $entity): ServiceResponse
    {
        return $this->executeWithFailover(
            fn (VtuProvider $provider, string $name) => $provider->purchaseAirtime($this->withProviderCode($entity, $name)),
            'purchaseAirtime',
            $entity,
        );
    }

    public function purchaseData(PurchaseData $entity): ServiceResponse
    {
        return $this->executeWithFailover(
            fn (VtuProvider $provider, string $name) => $provider->purchaseData($this->withProviderCode($entity, $name)),
            'purchaseData',
            $entity,
        );
    }

    public function purchaseCable(PurchaseCable $entity): ServiceResponse
    {
        return $this->executeWithFailover(
            fn (VtuProvider $provider, string $name) => $provider->purchaseCable($this->withProviderCode($entity, $name)),
            'purchaseCable',
            $entity,
        );
    }

    public function purchaseElectricity(PurchaseElectricity $entity): ServiceResponse
    {
        return $this->executeWithFailover(
            fn (VtuProvider $provider, string $name) => $provider->purchaseElectricity($this->withProviderCode($entity, $name)),
            'purchaseElectricity',
            $entity,
        );
    }

    public function purchaseExam(PurchaseExam $entity): ServiceResponse
    {
        return $this->executeWithFailover(
            fn (VtuProvider $provider, string $name) => $provider->purchaseExam($this->withProviderCode($entity, $name)),
            'purchaseExam',
            $entity,
        );
    }

    public function validateSmartcard(ValidateSmartcard $entity): ValidationResponse
    {
        return $this->executeWithFailover(
            fn (VtuProvider $provider) => $provider->validateSmartcard($entity),
            'validateSmartcard'
        );
    }

    public function validateMeter(ValidateMeter $entity): ValidationResponse
    {
        return $this->executeWithFailover(
            fn (VtuProvider $provider) => $provider->validateMeter($entity),
            'validateMeter'
        );
    }

    /**
     * Execute a callback across providers using failover logic.
     *
     * @template T
     *
     * @param  Closure(VtuProvider, string): T  $callback
     * @return T
     *
     * @throws Throwable
     */
    protected function executeWithFailover(Closure $callback, string $operation, mixed $entity = null)
    {
        $lastException = null;
        $lastResponse = null;

        $this->planCache = [];

        try {
            $availableProviders = $this->getAvailableProviders();

            if (empty($availableProviders)) {
                $this->resetDeadProviders();
                $availableProviders = $this->providers;
            }

            foreach ($availableProviders as $name => $provider) {
                try {
                    $response = $callback($provider, (string) $name);

                    if ($this->isSuccessfulResult($response)) {
                        return $response;
                    }

                    if ($this->failoverOnUnsuccessful && $this->isUnsuccessfulResult($response)) {
                        $this->logger->warning("VTU Provider [{$name}] returned unsuccessful response during [{$operation}], attempting failover.", [
                            'provider' => $name,
                            'operation' => $operation,
                            'response' => (array) $response,
                        ]);

                        $lastResponse = $response;
                        $this->markProviderAsDead((string) $name);

                        continue;
                    }

                    return $response;
                } catch (Throwable $e) {
                    $this->logger->error("VTU Provider [{$name}] failed during [{$operation}]: {$e->getMessage()}", [
                        'provider' => $name,
                        'operation' => $operation,
                        'exception' => $e,
                    ]);

                    $this->markProviderAsDead((string) $name);
                    $lastException = $e;
                }
            }

            if ($lastResponse !== null) {
                return $lastResponse;
            }

            throw $lastException ?? new RuntimeException("All VTU providers failed during [{$operation}].");
        } finally {
            $this->planCache = [];
        }
    }

    /**
     * Rebuild the plan-bound entity with a provider-specific apiCode.
     *
     * If the entity does not carry a plan reference, or the plan has no
     * dedicated code for the given provider, the entity is returned as-is
     * (its pre-resolved neutral apiCode is used).
     *
     * @template TEntity of object
     *
     * @param  TEntity  $entity
     * @return TEntity
     */
    protected function withProviderCode(object $entity, string $providerName): object
    {
        $plan = $this->loadPlanForEntity($entity);

        if ($plan === null || ! method_exists($plan, 'resolveApiCode')) {
            return $entity;
        }

        /** @var string|null $resolved */
        $resolved = $plan->resolveApiCode($providerName);

        if ($resolved === null || $resolved === '') {
            return $entity;
        }

        return match (true) {
            $entity instanceof PurchaseAirtime => new PurchaseAirtime(
                network: $resolved,
                amount: $entity->amount,
                mobileNumber: $entity->mobileNumber,
                portedNumber: $entity->portedNumber,
                reference: $entity->reference,
                planId: $entity->planId,
                planType: $entity->planType,
            ),
            $entity instanceof PurchaseData => new PurchaseData(
                network: $entity->network,
                mobileNumber: $entity->mobileNumber,
                apiCode: $resolved,
                reference: $entity->reference,
                planId: $entity->planId,
                planType: $entity->planType,
            ),
            $entity instanceof PurchaseCable => new PurchaseCable(
                service: $entity->service,
                smartcardNumber: $entity->smartcardNumber,
                apiCode: $resolved,
                amount: $entity->amount,
                reference: $entity->reference,
                planId: $entity->planId,
                planType: $entity->planType,
            ),
            $entity instanceof PurchaseElectricity => new PurchaseElectricity(
                service: $entity->service,
                meterNumber: $entity->meterNumber,
                meterType: $entity->meterType,
                apiCode: $resolved,
                amount: $entity->amount,
                reference: $entity->reference,
                planId: $entity->planId,
                planType: $entity->planType,
            ),
            $entity instanceof PurchaseExam => new PurchaseExam(
                service: $entity->service,
                apiCode: $resolved,
                amount: $entity->amount,
                numberOfPins: $entity->numberOfPins,
                reference: $entity->reference,
                planId: $entity->planId,
                planType: $entity->planType,
            ),
            default => $entity,
        };
    }

    protected function loadPlanForEntity(object $entity): ?Model
    {
        $planId = $entity->planId ?? null;
        $planType = $entity->planType ?? null;

        if ($planId === null || $planType === null || ! is_string($planType) || ! is_a($planType, Model::class, true)) {
            return null;
        }

        $cacheKey = "{$planType}|{$planId}";

        if (array_key_exists($cacheKey, $this->planCache)) {
            return $this->planCache[$cacheKey];
        }

        /** @var Model|null $plan */
        $plan = $planType::find($planId);

        $this->planCache[$cacheKey] = $plan;

        return $plan;
    }

    protected function isSuccessfulResult(mixed $response): bool
    {
        if ($response instanceof ServiceResponse) {
            return $response->isSuccessful();
        }

        if ($response instanceof ValidationResponse) {
            return $response->isValid();
        }

        return true;
    }

    protected function isUnsuccessfulResult(mixed $response): bool
    {
        return ! $this->isSuccessfulResult($response);
    }

    /**
     * Get list of providers that are currently active (not marked dead).
     *
     * @return array<string, VtuProvider>
     */
    public function getAvailableProviders(): array
    {
        $available = [];

        foreach ($this->providers as $name => $provider) {
            if (! $this->isProviderDead((string) $name)) {
                $available[$name] = $provider;
            }
        }

        return $available;
    }

    public function isProviderDead(string $name): bool
    {
        if (isset($this->deadProviders[$name])) {
            if ((microtime(true) - $this->deadProviders[$name]) > $this->retryAfter) {
                unset($this->deadProviders[$name]);
                $this->cache?->forget("vtu_dead_provider:{$name}");

                return false;
            }

            return true;
        }

        if ($this->cache?->has("vtu_dead_provider:{$name}")) {
            return true;
        }

        return false;
    }

    public function markProviderAsDead(string $name): void
    {
        $now = microtime(true);
        $this->deadProviders[$name] = $now;

        $this->cache?->put("vtu_dead_provider:{$name}", $now, $this->retryAfter);
    }

    public function resetDeadProviders(): void
    {
        foreach (array_keys($this->providers) as $name) {
            unset($this->deadProviders[$name]);
            $this->cache?->forget("vtu_dead_provider:{$name}");
        }
    }

    /**
     * Get all configured providers.
     *
     * @return array<string, VtuProvider>
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
