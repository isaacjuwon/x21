<?php

namespace App\Enums\Shares;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ShareOrderType: string implements HasColor, HasIcon, HasLabel
{
    case Buy = 'buy';
    case Sell = 'sell';

    public function getLabel(): string
    {
        return match ($this) {
            self::Buy => 'Buy',
            self::Sell => 'Sell',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Buy => 'success',
            self::Sell => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Buy => 'heroicon-o-arrow-down-circle',
            self::Sell => 'heroicon-o-arrow-up-circle',
        };
    }

    public function getFluxColor(): string
    {
        return match ($this) {
            self::Buy => 'green',
            self::Sell => 'red',
        };
    }

    public function getFluxIcon(): string
    {
        return match ($this) {
            self::Buy => 'arrow-down-circle',
            self::Sell => 'arrow-up-circle',
        };
    }
}
