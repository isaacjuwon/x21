<?php

namespace App\Enums\Kyc;

enum VerificationMode: string
{
    case Automatic = 'automatic';
    case Manual = 'manual';

    public function label(): string
    {
        return match($this) {
            self::Automatic => 'Automatic',
            self::Manual => 'Manual',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::Automatic => 'Instant verification via API',
            self::Manual => 'Verification via document upload',
        };
    }
}
