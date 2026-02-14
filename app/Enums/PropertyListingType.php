<?php

namespace App\Enums;

enum PropertyListingType: string
{
    case Sale = 'sale';
    case Rent = 'rent';

    public function getLabel(): string
    {
        return match ($this) {
            self::Sale => 'For Sale',
            self::Rent => 'For Rent',
        };
    }
}
