<?php

namespace App\Enums;

use App\Integrations\Paystack\PaystackConnector;
use App\Integrations\Paystack\Resources\PaymentResource;
use Illuminate\Support\Facades\App;

enum Payment: string
{
    case Paystack = 'paystack';

    public function resolve(): PaymentResource
    {
        return match($this) {
            self::Paystack => App::make(PaystackConnector::class)->payments(),
        };
    }
}
