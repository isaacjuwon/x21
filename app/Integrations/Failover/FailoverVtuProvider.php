<?php

declare(strict_types=1);

namespace App\Integrations\Failover;

use App\Integrations\Contracts\Providers\VtuProvider;
use App\Integrations\Epins\Entities\PurchaseAirtime;
use App\Integrations\Epins\Entities\PurchaseCable;
use App\Integrations\Epins\Entities\PurchaseData;
use App\Integrations\Epins\Entities\PurchaseElectricity;
use App\Integrations\Epins\Entities\PurchaseExam;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Integrations\Epins\Entities\ValidateMeter;
use App\Integrations\Epins\Entities\ValidateSmartcard;
use App\Integrations\Epins\Entities\ValidationResponse;
use Closure;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
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
            fn (VtuProvider $provider) => $provider->purchaseAirtime($entity),
            'purchaseAirtime'
        );
    }

    public function purchaseData(PurchaseData $entity): ServiceResponse
    {
        return $this->executeWithFailover(
            fn (VtuProvider $provider) => $provider->purchaseData($entity),
            'purchaseData'
        );
    }

    public function purchaseCable(PurchaseCable $entity): ServiceResponse
    {
        return $this->executeWithFailover(
            fn (VtuProvider $provider) => $provider->purchaseCable($entity),
            'purchaseCable'
        );
    }

    public function purchaseElectricity(PurchaseElectricity $entity): ServiceResponse
    {
        return $this->executeWithFailover(
            fn (VtuProvider $provider) => $provider->purchaseElectricity($entity),
            'purchaseElectricity'
        );
    }

    public function purchaseExam(PurchaseExam $entity): ServiceResponse
    {
        return $this->executeWithFailover(
            fn (VtuProvider $provider) => $provider->purchaseExam($entity),
            'purchaseExam'
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
    protected function executeWithFailover(Closure $callback, string $operation)
    {
        $lastException = null;
        $lastResponse = null;

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
