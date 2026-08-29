<?php

declare(strict_types=1);

namespace App\Enums\Banners;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BannerLocation: string implements HasColor, HasLabel
{
    case Dashboard = 'dashboard';
    case Wallet = 'wallet';
    case Loans = 'loans';
    case Shares = 'shares';
    case Services = 'services';
    case Kyc = 'kyc';

    public function getLabel(): string
    {
        return match ($this) {
            self::Dashboard => 'Dashboard',
            self::Wallet => 'Wallet',
            self::Loans => 'Loans',
            self::Shares => 'Shares',
            self::Services => 'Services',
            self::Kyc => 'KYC',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Dashboard => 'primary',
            self::Wallet => 'success',
            self::Loans => 'warning',
            self::Shares => 'info',
            self::Services => 'violet',
            self::Kyc => 'orange',
        };
    }
}
