<?php

declare(strict_types=1);

namespace App\Integrations\KudiSms;

use App\Enums\Http\Method;
use App\Integrations\KudiSms\Resources\SmsResource;
use App\Settings\IntegrationSettings;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

final readonly class KudiSmsConnector
{
    public function __construct(
        private PendingRequest $request,
    ) {}

    public function sms(): SmsResource
    {
        return new SmsResource(connector: $this);
    }

    public function send(Method $method, string $uri, array $options = []): Response
    {
        $httpMethod = strtolower($method->value);

        return $this->request->{$httpMethod}(
            $uri,
            $options
        )->throw();
    }

    public static function register(Application $app): void
    {
        $app->bind(
            abstract: KudiSmsConnector::class,
            concrete: function () {
                $settings = app(IntegrationSettings::class);

                return new KudiSmsConnector(
                    request: Http::baseUrl(
                        url: ! blank($settings->kudisms_url) ? $settings->kudisms_url : config('services.kudisms.url', 'https://my.kudisms.net'),
                    )->timeout(
                        seconds: 30,
                    )->asJson()->acceptJson(),
                );
            },
        );
    }
}
