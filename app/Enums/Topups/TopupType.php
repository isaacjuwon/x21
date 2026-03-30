<?php

namespace App\Enums\Topups;

enum TopupType: string
{
    case Airtime = 'airtime';
    case Data = 'data';
    case Cable = 'cable';
    case Education = 'education';
    case Electricity = 'electricity';

    public function getLabel(): string
    {
        return match ($this) {
            self::Airtime => __('Airtime'),
            self::Data => __('Data'),
            self::Cable => __('Cable TV'),
            self::Education => __('Education'),
            self::Electricity => __('Electricity'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Airtime => 'heroicon-o-phone',
            self::Data => 'heroicon-o-globe-alt',
            self::Cable => 'heroicon-o-tv',
            self::Education => 'heroicon-o-academic-cap',
            self::Electricity => 'heroicon-o-bolt',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Airtime => 'blue',
            self::Data => 'green',
            self::Cable => 'purple',
            self::Education => 'amber',
            self::Electricity => 'yellow',
        };
    }
}
