<?php

namespace App\Enums\Wallets;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum WalletType: string implements HasColor, HasIcon, HasLabel
{
    case General = 'general';

    public function getLabel(): string
    {
        return match ($this) {
            self::General => 'General',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::General => 'primary',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::General => 'heroicon-o-wallet',
        };
    }

    public function getFluxColor(): string
    {
        return match ($this) {
            self::General => 'violet',
        };
    }

    public function getFluxIcon(): string
    {
        return match ($this) {
            self::General => 'wallet',
        };
    }
}
