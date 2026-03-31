<?php

namespace App\Enums\Loans;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum LoanScheduleEntryStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'Pending';
    case Paid = 'Paid';
    case Overdue = 'Overdue';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Overdue => 'Overdue',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Paid => 'success',
            self::Overdue => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending => 'heroicon-o-clock',
            self::Paid => 'heroicon-o-check-circle',
            self::Overdue => 'heroicon-o-exclamation-circle',
        };
    }

    public function getFluxColor(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Paid => 'green',
            self::Overdue => 'red',
        };
    }

    public function getFluxIcon(): string
    {
        return match ($this) {
            self::Pending => 'clock',
            self::Paid => 'check-circle',
            self::Overdue => 'exclamation-circle',
        };
    }
}
