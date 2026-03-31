<?php

namespace App\Webhooks\Verifiers;

use App\Webhooks\Contracts\WebhookVerifierContract;
use Illuminate\Http\Request;

class PaystackWebhookVerifier implements WebhookVerifierContract
{
    public function verify(Request $request): bool
    {
        $signature = $request->header('x-paystack-signature');

        if (! $signature) {
            return false;
        }

        $secret = config('services.paystack.secret_key');

        return hash_equals(
            hash_hmac('sha512', $request->getContent(), $secret),
            $signature
        );
    }
}
