<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ShareSettings extends Settings
{
    public float $price_per_share;
    public int $min_shares_purchase;
    public int $max_shares_per_user;
    public int $holding_period_days;

    public static function group(): string
    {
        return 'shares';
    }
}
