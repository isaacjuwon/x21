<?php

declare(strict_types=1);

namespace App\Enums\Plans;

use App\Enums\Topups\TopupType;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ServiceType: string implements HasColor, HasIcon, HasLabel
{
    case Airtime = 'airtime';
    case Data = 'data';
    case Cable = 'cable';
    case Electricity = 'electricity';
    case Education = 'education';
    case Exam = 'exam';

    public function getLabel(): string
    {
        return match ($this) {
            self::Airtime => __('Airtime'),
            self::Data => __('Data Bundle'),
            self::Cable => __('Cable TV'),
            self::Electricity => __('Electricity'),
            self::Education => __('Education'),
            self::Exam => __('Exam PIN'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Airtime => 'info',
            self::Data => 'success',
            self::Cable => 'primary',
            self::Electricity => 'warning',
            self::Education => 'gray',
            self::Exam => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Airtime => 'heroicon-o-phone',
            self::Data => 'heroicon-o-signal',
            self::Cable => 'heroicon-o-tv',
            self::Electricity => 'heroicon-o-bolt',
            self::Education => 'heroicon-o-academic-cap',
            self::Exam => 'heroicon-o-identification',
        };
    }

    /**
     * Convert to matching TopupType enum.
     */
    public function toTopupType(): TopupType
    {
        return match ($this) {
            self::Airtime => TopupType::Airtime,
            self::Data => TopupType::Data,
            self::Cable => TopupType::Cable,
            self::Electricity => TopupType::Electricity,
            self::Education, self::Exam => TopupType::Education,
        };
    }
}
