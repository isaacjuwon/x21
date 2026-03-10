<?php

declare(strict_types=1);

namespace App\Enums\Connectors;

use App\Integrations\Paystack\PaystackConnector;

enum PaymentConnector: string
{
    case Paystack = 'paystack';

    public function connector(): PaystackConnector
    {
        return match ($this) {
            self::Paystack => app(PaystackConnector::class),
        };
    }

    public static function default(): self
    {
        return self::Paystack;
    }

    public static function register(\Illuminate\Contracts\Foundation\Application $app): void
    {
        PaystackConnector::register($app);
    }
}
