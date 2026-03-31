<?php

namespace App\Events\Webhooks;

use App\Models\WebhookLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebhookReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(public WebhookLog $log) {}
}
