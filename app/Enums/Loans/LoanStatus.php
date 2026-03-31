<?php

namespace App\Enums\Loans;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum LoanStatus: string implements HasColor, HasIcon, HasLabel
{
    case Active = 'active';
    case Approved = 'approved';
    case Disbursed = 'disbursed';
    case Rejected = 'rejected';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Approved => 'Approved',
            self::Disbursed => 'Disbursed',
            self::Rejected => 'Rejected',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'success',
            self::Approved => 'info',
            self::Disbursed => 'primary',
            self::Rejected => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Active => 'heroicon-o-check-circle',
            self::Approved => 'heroicon-o-hand-thumb-up',
            self::Disbursed => 'heroicon-o-banknotes',
            self::Rejected => 'heroicon-o-x-circle',
        };
    }

    public function getFluxColor(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Approved => 'blue',
            self::Disbursed => 'violet',
            self::Rejected => 'red',
        };
    }

    public function getFluxIcon(): string
    {
        return match ($this) {
            self::Active => 'check-circle',
            self::Approved => 'hand-thumb-up',
            self::Disbursed => 'banknotes',
            self::Rejected => 'x-circle',
        };
    }
}
