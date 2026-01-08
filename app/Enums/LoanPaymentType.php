<?php

namespace App\Enums;

enum LoanPaymentType: string
{
    case SCHEDULED = 'scheduled';
    case EARLY = 'early';
    case PENALTY = 'penalty';

    public function getLabel(): string
    {
        return match ($this) {
            self::SCHEDULED => 'Scheduled Payment',
            self::EARLY => 'Early Payment',
            self::PENALTY => 'Penalty Payment',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::SCHEDULED => 'primary',
            self::EARLY => 'success',
            self::PENALTY => 'danger',
        };
    }
}
