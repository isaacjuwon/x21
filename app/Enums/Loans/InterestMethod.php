<?php

namespace App\Enums\Loans;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum InterestMethod: string implements HasColor, HasIcon, HasLabel
{
    case FlatRate = 'FlatRate';
    case ReducingBalance = 'ReducingBalance';

    public function getLabel(): string
    {
        return match ($this) {
            self::FlatRate => 'Flat Rate',
            self::ReducingBalance => 'Reducing Balance',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::FlatRate => 'warning',
            self::ReducingBalance => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::FlatRate => 'heroicon-o-minus',
            self::ReducingBalance => 'heroicon-o-arrow-trending-down',
        };
    }
}
