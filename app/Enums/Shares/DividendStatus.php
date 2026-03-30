<?php

namespace App\Enums\Shares;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum DividendStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Distributed = 'distributed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Distributed => 'Distributed',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Distributed => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending => 'heroicon-o-clock',
            self::Distributed => 'heroicon-o-check-circle',
        };
    }
}
