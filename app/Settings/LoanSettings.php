<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class LoanSettings extends Settings
{
    public float $loan_to_shares_ratio;

    public static function group(): string
    {
        return 'loans';
    }
}
