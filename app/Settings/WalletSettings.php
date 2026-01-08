<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WalletSettings extends Settings
{
    public bool $wallet_enabled;

    public int $min_funding_amount;

    public int $max_funding_amount;

    public int $min_withdrawal_amount;

    public int $max_withdrawal_amount;

    public float $withdrawal_fee_percentage;

    public int $withdrawal_fee_cap;

    public bool $instant_withdrawal_enabled;

    public int $withdrawal_processing_hours;

    public int $daily_withdrawal_limit;

    public int $monthly_withdrawal_limit;

    public static function group(): string
    {
        return 'wallet';
    }
}
