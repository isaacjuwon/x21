<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class VerificationSettings extends Settings
{
    public string $kyc_verification_mode = 'automatic'; // 'automatic' or 'manual'

    public static function group(): string
    {
        return 'verification';
    }
}
