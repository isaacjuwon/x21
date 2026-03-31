<?php

namespace App\Webhooks\Contracts;

use Illuminate\Http\Request;

interface WebhookVerifierContract
{
    public function verify(Request $request): bool;
}
