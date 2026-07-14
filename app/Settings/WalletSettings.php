<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WalletSettings extends Settings
{
    public float $min_withdrawal;

    public float $max_withdrawal;

    public float $withdrawal_fee;

    public static function group(): string
    {
        return 'wallet';
    }
}
