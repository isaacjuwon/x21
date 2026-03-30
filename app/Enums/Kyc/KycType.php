<?php

namespace App\Enums\Kyc;

enum KycType: string
{
    case Nin = 'nin';
    case Bvn = 'bvn';

    public function getLabel(): string
    {
        return match ($this) {
            self::Nin => 'NIN (Simple Verification)',
            self::Bvn => 'BVN (Advanced Verification)',
        };
    }
}
