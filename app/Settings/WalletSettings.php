<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WalletSettings extends Settings
{
    public float $min_withdrawal;

    public float $withdrawal_fee;

    public float $stamp_duty_rate;

    public float $stamp_duty_threshold;

    public static function group(): string
    {
        return 'wallet';
    }
}
