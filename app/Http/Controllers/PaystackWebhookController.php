<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\Paystack\PaymentFailed;
use App\Events\Paystack\PaymentSuccessful;
use App\Events\Paystack\TransferFailed;
use App\Events\Paystack\TransferSuccessful;
use App\Integrations\Paystack\PaystackConnector;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

final class PaystackWebhookController
{
    public function __construct(
        private PaystackConnector $paystack,
    ) {}

    public function __invoke(Request $request): Response
    {
        Log::info('Paystack Webhook Received', [
            'event' => $request->input('event'),
            'ip' => $request->ip(),
        ]);

        // Verify webhook signature
        try {
            $this->paystack->webhooks()->verify(
                signature: $request->header('x-paystack-signature'),
                payload: $request->getContent(),
            );
            Log::info('Paystack Webhook Signature Verified');
        } catch (\Exception $e) {
            Log::error('Paystack Webhook Signature Verification Failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        // Parse payload
        $payload = $this->paystack->webhooks()->parse($request->all());
        Log::info('Paystack Webhook Parsed', ['event' => $payload->event]);

        // Dispatch events based on webhook type
        $eventDispatched = match ($payload->event) {
            'charge.success', 'paymentrequest.success' => event(new PaymentSuccessful($payload->data)),
            'charge.failed' => event(new PaymentFailed($payload->data)),
            'transfer.success' => event(new TransferSuccessful($payload->data)),
            'transfer.failed' => event(new TransferFailed($payload->data)),
            default => null,
        };

        if ($eventDispatched) {
            Log::info('Paystack Event Dispatched', [
                'event' => $payload->event,
                'reference' => $payload->data['reference'] ?? null,
            ]);
        } else {
            Log::warning('Paystack Webhook Event Not Handled', ['event' => $payload->event]);
        }

        return response()->noContent();
    }
}
