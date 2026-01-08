<?php

declare(strict_types=1);

namespace App\Enums\Connectors;

use App\Integrations\Paystack\PaystackConnector;

enum PaymentConnector: string
{
    case PAYSTACK = 'paystack';

    public function connector(): PaystackConnector
    {
        return match ($this) {
            self::PAYSTACK => app(PaystackConnector::class),
        };
    }

    public static function default(): self
    {
        return self::PAYSTACK;
    }

    public static function register(\Illuminate\Contracts\Foundation\Application $app): void
    {
        PaystackConnector::register($app);
    }
}
