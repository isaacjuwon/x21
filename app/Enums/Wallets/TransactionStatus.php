<?php

namespace App\Enums\Wallets;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TransactionStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Voided = 'voided';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Completed => 'Completed',
            self::Voided => 'Voided',
            self::Failed => 'Failed',
            self::Refunded => 'Refunded',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Completed => 'success',
            self::Voided => 'gray',
            self::Failed => 'danger',
            self::Refunded => 'info',
        };
    }

    public function getFluxColor(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Completed => 'green',
            self::Voided => 'zinc',
            self::Failed => 'red',
            self::Refunded => 'blue',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending => 'heroicon-o-clock',
            self::Completed => 'heroicon-o-check-circle',
            self::Voided => 'heroicon-o-x-circle',
            self::Failed => 'heroicon-o-exclamation-circle',
            self::Refunded => 'heroicon-o-arrow-uturn-left',
        };
    }

    public function getFluxIcon(): string
    {
        return match ($this) {
            self::Pending => 'clock',
            self::Completed => 'check-circle',
            self::Voided => 'x-circle',
            self::Failed => 'exclamation-circle',
            self::Refunded => 'arrow-uturn-left',
        };
    }

    /** Whether this status is terminal (no further transitions allowed). */
    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Voided, self::Failed, self::Refunded]);
    }
}
