<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ShareSettings extends Settings
{
    public bool $require_admin_approval;

    public float $share_price;

    public int $holding_period;

    public static function group(): string
    {
        return 'shares';
    }
}
