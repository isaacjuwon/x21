<?php

declare(strict_types=1);

namespace App\Integrations\Vtugate;

use App\Enums\Http\Method;
use App\Integrations\Vtugate\Exceptions\VtugateException;
use App\Integrations\Vtugate\Resources\AirtimeResource;
use App\Integrations\Vtugate\Resources\CableResource;
use App\Integrations\Vtugate\Resources\DataResource;
use App\Integrations\Vtugate\Resources\EducationResource;
use App\Integrations\Vtugate\Resources\ElectricityResource;
use App\Integrations\Vtugate\Resources\PlansResource;
use App\Integrations\Vtugate\Resources\WalletResource;
use App\Settings\IntegrationSettings;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

final readonly class VtugateConnector
{
    public function __construct(
        private PendingRequest $request,
        private ?string $baseUrl = null,
        private ?string $apiKey = null,
    ) {}

    public function airtime(): AirtimeResource
    {
        return new AirtimeResource(connector: $this);
    }

    public function data(): DataResource
    {
        return new DataResource(connector: $this);
    }

    public function cable(): CableResource
    {
        return new CableResource(connector: $this);
    }

    public function education(): EducationResource
    {
        return new EducationResource(connector: $this);
    }

    public function electricity(): ElectricityResource
    {
        return new ElectricityResource(connector: $this);
    }

    public function wallet(): WalletResource
    {
        return new WalletResource(connector: $this);
    }

    public function plans(): PlansResource
    {
        return new PlansResource(connector: $this);
    }

    public function generateRequestId(): string
    {
        return date('YmdHi').substr(hash('sha256', (string) microtime(true)), 0, 10);
    }

    public function getBaseUrl(): string
    {
        if (! empty($this->baseUrl)) {
            return $this->baseUrl;
        }

        try {
            $settings = app(IntegrationSettings::class);
            if (! empty($settings->vtugate_url)) {
                return $settings->vtugate_url;
            }
        } catch (Throwable) {
        }

        return config('services.vtugate.url', 'https://api.vtugate.com');
    }

    public function getApiKey(): string
    {
        if (! empty($this->apiKey)) {
            return $this->apiKey;
        }

        try {
            $settings = app(IntegrationSettings::class);
            if (! empty($settings->vtugate_api_key)) {
                return $settings->vtugate_api_key;
            }
        } catch (Throwable) {
        }

        return (string) config('services.vtugate.api_key', '');
    }

    /**
     * Create a pre-configured PendingRequest inside an Http::pool.
     */
    public function pooledRequest(Pool $pool, ?string $as = null): PendingRequest
    {
        $request = $as !== null ? $pool->as($as) : $pool;

        $req = $request->baseUrl($this->getBaseUrl())
            ->timeout(60)
            ->asForm()
            ->acceptJson();

        $token = $this->getApiKey();
        if ($token !== '') {
            $req->withToken($token);
        }

        return $req;
    }

    public function send(Method $method, string $uri, array $options = []): Response
    {
        try {
            $httpMethod = strtolower($method->value);

            return $this->request->{$httpMethod}(
                $uri,
                $options
            )->throw();
        } catch (Throwable $exception) {
            throw new VtugateException(
                message: 'VtuGate API error: '.$exception->getMessage(),
                code: (int) $exception->getCode(),
                previous: $exception,
            );
        }
    }

    public static function register(Application $app): void
    {
        $app->bind(
            abstract: VtugateConnector::class,
            concrete: function () {
                $url = config('services.vtugate.url', 'https://api.vtugate.com');
                $apiKey = config('services.vtugate.api_key', '');

                try {
                    $settings = app(IntegrationSettings::class);
                    if (! empty($settings->vtugate_url)) {
                        $url = $settings->vtugate_url;
                    }
                    if (! empty($settings->vtugate_api_key)) {
                        $apiKey = $settings->vtugate_api_key;
                    }
                } catch (Throwable) {
                    // Fallback to config values if settings cannot be loaded
                }

                return new VtugateConnector(
                    request: Http::baseUrl($url)
                        ->timeout(60)
                        ->withToken($apiKey)
                        ->asForm()
                        ->acceptJson(),
                    baseUrl: $url,
                    apiKey: $apiKey,
                );
            },
        );
    }
}
