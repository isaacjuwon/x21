<?php

namespace App\Webhooks\Contracts;

use App\Models\WebhookLog;

interface WebhookProcessorContract
{
    public function handle(array $payload, WebhookLog $log): void;
}
