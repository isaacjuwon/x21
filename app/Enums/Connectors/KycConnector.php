<?php

declare(strict_types=1);

namespace App\Enums\Connectors;

use App\Integrations\Dojah\DojahConnector;

enum KycConnector: string
{
    case Dojah = 'dojah';

    public function connector(): DojahConnector
    {
        return match ($this) {
            self::Dojah => app(DojahConnector::class),
        };
    }

    public static function default(): self
    {
    return self::Dojah;
    }

    public static function register(\Illuminate\Contracts\Foundation\Application $app): void
    {
        // If DojahConnector needs registration logic, add here
    }
}
