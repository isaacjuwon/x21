<?php

declare(strict_types=1);

namespace App\Integrations\Epins;

use App\Enums\Http\Method;
use App\Integrations\Epins\Resources\AirtimeResource;
use App\Integrations\Epins\Resources\CableResource;
use App\Integrations\Epins\Resources\DataResource;
use App\Integrations\Epins\Resources\EducationResource;
use App\Integrations\Epins\Resources\ElectricityResource;
use App\Integrations\Epins\Resources\WalletResource;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

final readonly class EpinsConnector
{
    public function __construct(
        private PendingRequest $request,
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

    public function send(Method $method, string $uri, array $options = []): Response
    {
        return $this->request->send(
            method: $method->value,
            url: $uri,
            options: $options,
        )->throw();
    }

    public static function register(Application $app): void
    {
        $app->bind(
            abstract: EpinsConnector::class,
            concrete: fn () => new EpinsConnector(
                request: Http::baseUrl(
                    url: config('services.epins.url'),
                )->timeout(
                    seconds: 30,
                )->withToken(
                    token: config('services.epins.api_key'),
                )->asJson()->acceptJson(),
            ),
        );
    }
}
