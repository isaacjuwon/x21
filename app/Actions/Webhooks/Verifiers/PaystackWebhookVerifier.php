<?php

namespace App\Actions\Webhooks\Verifiers;

use App\Models\WebhookLog;
use Illuminate\Http\Request;

class PaystackWebhookVerifier
{
    public function handle(Request $request, WebhookLog $log): bool
    {
        $signature = $request->header('x-paystack-signature');
        if (! $signature) {
            return false;
        }

        $secret = config('services.paystack.secret_key');

        return hash_hmac('sha512', $request->getContent(), $secret) === $signature;
    }
}
