<?php

declare(strict_types=1);

namespace App\Integrations\Dojah;

use Illuminate\Http\Client\PendingRequest;
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

    public function send(string $method, string $uri, array $options = []): \Illuminate\Http\Client\Response
    {
        $settings = app(\App\Settings\IntegrationSettings::class);

        $baseUrl = $settings->dojah_base_url ?? config('services.dojah.base_url', 'https://api.dojah.io');
        $apiKey = $settings->dojah_api_key ?? config('services.dojah.api_key');
        $appId = $settings->dojah_app_id ?? config('services.dojah.app_id');

        $request = $this->request->baseUrl($baseUrl)
            ->withHeaders([
                'AppId' => $appId,
                'Authorization' => "Bearer {$apiKey}",
                'Accept' => 'application/json',
            ]);

        return $request->{$method}($uri, $options['json'] ?? []);
    }
}
