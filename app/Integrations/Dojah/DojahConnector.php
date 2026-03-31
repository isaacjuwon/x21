<?php

declare(strict_types=1);

namespace App\Integrations\Dojah;

use App\Settings\IntegrationSettings;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

final readonly class DojahConnector
{
    public function __construct(
        private PendingRequest $request,
    ) {}

    public function verification(): Resources\VerificationResource
    {
        return new Resources\VerificationResource(connector: $this);
    }

    public function send(string $method, string $uri, array $options = []): Response
    {
        $httpMethod = strtolower($method);

        if ($httpMethod === 'get') {
            return $this->request->get($uri, $options['query'] ?? []);
        }

        return $this->request->{$httpMethod}($uri, $options['json'] ?? []);
    }

    public static function register(Application $app): void
    {
        $app->bind(
            abstract: DojahConnector::class,
            concrete: function () {
                $settings = app(IntegrationSettings::class);

                return new DojahConnector(
                    request: Http::baseUrl(
                        url: ! blank($settings->dojah_base_url) ? $settings->dojah_base_url : config('services.dojah.base_url', 'https://api.dojah.io'),
                    )->timeout(
                        seconds: 30,
                    )->withHeaders([
                        'AppId' => ! blank($settings->dojah_app_id) ? $settings->dojah_app_id : config('services.dojah.app_id'),
                        'Authorization' => 'Bearer '.(! blank($settings->dojah_api_key) ? $settings->dojah_api_key : config('services.dojah.api_key')),
                    ])->asJson()->acceptJson(),
                );
            },
        );
    }
}
