<?php

declare(strict_types=1);

namespace App\Enums\Connectors;

use App\Integrations\Epins\EpinsConnector;

enum VtuConnector: string
{
    case Epins = 'epins';

    public function connector(): EpinsConnector
    {
        return match ($this) {
            self::Epins => app(EpinsConnector::class),
        };
    }

    public static function default(): self
    {
        return self::Epins;
    }

    public static function register(\Illuminate\Contracts\Foundation\Application $app): void
    {
        EpinsConnector::register($app);
    }
}
