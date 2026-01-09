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

final class PaystackWebhookController
{
    public function __construct(
        private PaystackConnector $paystack,
    ) {}

    public function __invoke(Request $request): Response
    {
        // Verify webhook signature
        $this->paystack->webhooks()->verify(
            signature: $request->header('x-paystack-signature'),
            payload: $request->getContent(),
        );

        // Parse payload
        $payload = $this->paystack->webhooks()->parse($request->all());

        // Dispatch events based on webhook type
        match ($payload->event) {
            'charge.success', 'paymentrequest.success' => event(new PaymentSuccessful($payload->data)),
            'charge.failed' => event(new PaymentFailed($payload->data)),
            'transfer.success' => event(new TransferSuccessful($payload->data)),
            'transfer.failed' => event(new TransferFailed($payload->data)),
            default => null,
        };

        return response()->noContent();
    }
}
