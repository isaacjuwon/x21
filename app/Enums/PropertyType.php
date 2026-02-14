<?php

namespace App\Enums;

enum PropertyType: string
{
    case Apartment = 'apartment';
    case House = 'house';
    case Condo = 'condo';
    case Land = 'land';
    case Commercial = 'commercial';
    case Townhouse = 'townhouse';
    case Villa = 'villa';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Apartment => 'Apartment',
            self::House => 'House',
            self::Condo => 'Condo',
            self::Land => 'Land',
            self::Commercial => 'Commercial',
            self::Townhouse => 'Townhouse',
            self::Villa => 'Villa',
            self::Other => 'Other',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Apartment => 'fa-building',
            self::House => 'fa-home',
            self::Condo => 'fa-city',
            self::Townhouse => 'fa-house-user',
            self::Villa => 'fa-vihara',
            default => 'fa-building',
        };
    }
}
