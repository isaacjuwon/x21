<?php

namespace App\Enums\Wallets;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TransactionType: string implements HasColor, HasIcon, HasLabel
{
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';
    case Hold = 'hold';

    public function getLabel(): string
    {
        return match ($this) {
            self::Deposit => 'Deposit',
            self::Withdrawal => 'Withdrawal',
            self::Hold => 'Hold',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Deposit => 'success',
            self::Withdrawal => 'danger',
            self::Hold => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Deposit => 'heroicon-o-arrow-down-circle',
            self::Withdrawal => 'heroicon-o-arrow-up-circle',
            self::Hold => 'heroicon-o-lock-closed',
        };
    }

    public function isWithdrawal(): bool
    {
        return $this === self::Withdrawal;
    }

    public function isDeposit(): bool
    {
        return $this === self::Deposit;
    }

    public function getFluxColor(): string
    {
        return match ($this) {
            self::Deposit => 'green',
            self::Withdrawal => 'red',
            self::Hold => 'yellow',
        };
    }

    public function getFluxIcon(): string
    {
        return match ($this) {
            self::Deposit => 'arrow-down-circle',
            self::Withdrawal => 'arrow-up-circle',
            self::Hold => 'lock-closed',
        };
    }
}
