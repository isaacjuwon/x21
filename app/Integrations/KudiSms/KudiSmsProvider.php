<?php

namespace App\Integrations\KudiSms;

use App\Integrations\Contracts\Providers\SmsProvider;
use App\Integrations\KudiSms\Entities\SendSms;
use App\Integrations\KudiSms\Entities\SmsResponse;

class KudiSmsProvider implements SmsProvider
{
    public function __construct(
        protected KudiSmsConnector $connector,
    ) {}

    public function sendSms(SendSms $entity): SmsResponse
    {
        return $this->connector->sms()->send($entity);
    }
}
