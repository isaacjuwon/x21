<?php

namespace App\Enums\Kyc;

enum KycMethod: string
{
    case Manual = 'manual';
    case Automatic = 'automatic';

    public function getLabel(): string
    {
        return match ($this) {
            self::Manual => 'Manual Upload',
            self::Automatic => 'Automatic Verification',
        };
    }
}
