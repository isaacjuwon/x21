<?php

namespace App\Integrations\Contracts\Providers;

use App\Integrations\KudiSms\Entities\SendSms;
use App\Integrations\KudiSms\Entities\SmsResponse;

interface SmsProvider
{
    public function sendSms(SendSms $entity): SmsResponse;
}
