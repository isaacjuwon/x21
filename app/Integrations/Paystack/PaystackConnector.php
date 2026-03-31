<?php

declare(strict_types=1);

namespace App\Integrations\Paystack;

use App\Enums\Http\Method;
use App\Integrations\Paystack\Resources\BankResource;
use App\Integrations\Paystack\Resources\TransactionResource;
use App\Integrations\Paystack\Resources\TransferResource;
use App\Integrations\Paystack\Resources\WebhookResource;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

final readonly class PaystackConnector
{
    public function __construct(
        private PendingRequest $request,
    ) {}

    public function transactions(): TransactionResource
    {
        return new TransactionResource(connector: $this);
    }

    public function transfers(): TransferResource
    {
        return new TransferResource(connector: $this);
    }

    public function webhooks(): WebhookResource
    {
        return new WebhookResource(connector: $this);
    }

    public function bank(): BankResource
    {
        return new BankResource(connector: $this);
    }

    public function recipients(): Resources\RecipientResource
    {
        return new Resources\RecipientResource(connector: $this);
    }

    public function send(Method $method, string $uri, array $options = []): Response
    {
        if ($method === Method::Get && ! empty($options) && ! isset($options['query'])) {
            $options = ['query' => $options];
        }

        return $this->request->send(
            method: $method->value,
            url: $uri,
            options: $options,
        )->throw();
    }

    public static function register(Application $app): void
    {
        $app->bind(
            abstract: PaystackConnector::class,
            concrete: function () {
                $settings = app(\App\Settings\IntegrationSettings::class);

                return new PaystackConnector(
                    request: Http::baseUrl(
                        url: ! blank($settings->paystack_url) ? $settings->paystack_url : config('services.paystack.url'),
                    )->timeout(
                        seconds: 30,
                    )->withToken(
                        token: ! blank($settings->paystack_secret_key) ? $settings->paystack_secret_key : config('services.paystack.secret_key'),
                    )->asJson()->acceptJson(),
                );
            },
        );
    }
}
