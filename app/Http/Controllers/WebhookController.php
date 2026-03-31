<?php

namespace App\Http\Controllers;

use App\Actions\Webhooks\ProcessEpinsWebhookAction;
use App\Actions\Webhooks\ProcessPaystackWebhookAction;
use App\Webhooks\Verifiers\PaystackWebhookVerifier;
use App\Webhooks\Webhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handle(string $provider, Request $request): JsonResponse
    {
        return match ($provider) {
            'paystack' => Webhook::receive('paystack', $request)
                ->verify(PaystackWebhookVerifier::class)
                ->process(ProcessPaystackWebhookAction::class)
                ->onQueue('webhooks')
                ->handle(),

            'epins' => Webhook::receive('epins', $request)
                ->process(ProcessEpinsWebhookAction::class)
                ->onQueue('webhooks')
                ->handle(),

            default => response()->json(['message' => 'Provider not supported'], 404),
        };
    }
}
