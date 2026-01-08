<?php

declare(strict_types=1);

namespace App\Enums\Connectors;

use App\Integrations\Epins\EpinsConnector;

enum VtuConnector: string
{
    case EPINS = 'epins';

    public function connector(): EpinsConnector
    {
        return match ($this) {
            self::EPINS => app(EpinsConnector::class),
        };
    }

    public static function default(): self
    {
        return self::EPINS;
    }

    public static function register(\Illuminate\Contracts\Foundation\Application $app): void
    {
        EpinsConnector::register($app);
    }
}
