<?php

declare(strict_types=1);

namespace App\Integrations\Vtpass;

use App\Enums\Http\Method;
use App\Integrations\Vtpass\Exceptions\VtpassException;
use App\Integrations\Vtpass\Resources\AirtimeResource;
use App\Integrations\Vtpass\Resources\CableResource;
use App\Integrations\Vtpass\Resources\DataResource;
use App\Integrations\Vtpass\Resources\EducationResource;
use App\Integrations\Vtpass\Resources\ElectricityResource;
use App\Integrations\Vtpass\Resources\PlansResource;
use App\Integrations\Vtpass\Resources\WalletResource;
use App\Settings\IntegrationSettings;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

final readonly class VtpassConnector
{
    public function __construct(
        private PendingRequest $request,
        private ?string $baseUrl = null,
        private ?array $headers = null,
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
            if (! empty($settings->vtpass_url)) {
                return $settings->vtpass_url;
            }
        } catch (Throwable) {
        }

        return config('services.vtpass.url', 'https://vtpass.com/api');
    }

    public function getHeaders(): array
    {
        if (! empty($this->headers)) {
            return $this->headers;
        }

        $apiKey = config('services.vtpass.api_key', '');
        $secretKey = config('services.vtpass.secret_key', '');
        $publicKey = config('services.vtpass.public_key', '');

        try {
            $settings = app(IntegrationSettings::class);
            if (! empty($settings->vtpass_api_key)) {
                $apiKey = $settings->vtpass_api_key;
            }
            if (! empty($settings->vtpass_secret_key)) {
                $secretKey = $settings->vtpass_secret_key;
            }
            if (! empty($settings->vtpass_public_key)) {
                $publicKey = $settings->vtpass_public_key;
            }
        } catch (Throwable) {
        }

        return array_filter([
            'api-key' => $apiKey,
            'secret-key' => $secretKey,
            'public-key' => $publicKey,
        ]);
    }

    /**
     * Create a pre-configured PendingRequest inside an Http::pool.
     */
    public function pooledRequest(Pool $pool, ?string $as = null): PendingRequest
    {
        $req = $as !== null ? $pool->as($as) : $pool;

        return $req->baseUrl($this->getBaseUrl())
            ->timeout(60)
            ->withHeaders($this->getHeaders())
            ->asJson()
            ->acceptJson();
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
            throw new VtpassException(
                message: 'VTPass API error: '.$exception->getMessage(),
                code: (int) $exception->getCode(),
                previous: $exception,
            );
        }
    }

    public static function register(Application $app): void
    {
        $app->bind(
            abstract: VtpassConnector::class,
            concrete: function () {
                $url = config('services.vtpass.url', 'https://vtpass.com/api');
                $apiKey = config('services.vtpass.api_key', '');
                $secretKey = config('services.vtpass.secret_key', '');
                $publicKey = config('services.vtpass.public_key', '');

                try {
                    $settings = app(IntegrationSettings::class);
                    if (! empty($settings->vtpass_url)) {
                        $url = $settings->vtpass_url;
                    }
                    if (! empty($settings->vtpass_api_key)) {
                        $apiKey = $settings->vtpass_api_key;
                    }
                    if (! empty($settings->vtpass_secret_key)) {
                        $secretKey = $settings->vtpass_secret_key;
                    }
                    if (! empty($settings->vtpass_public_key)) {
                        $publicKey = $settings->vtpass_public_key;
                    }
                } catch (Throwable) {
                    // Fallback to config values if settings cannot be loaded
                }

                $headers = array_filter([
                    'api-key' => $apiKey,
                    'secret-key' => $secretKey,
                    'public-key' => $publicKey,
                ]);

                return new VtpassConnector(
                    request: Http::baseUrl($url)
                        ->timeout(60)
                        ->withHeaders($headers)
                        ->asJson()
                        ->acceptJson(),
                    baseUrl: $url,
                    headers: $headers,
                );
            },
        );
    }
}
