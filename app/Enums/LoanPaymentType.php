<?php

namespace App\Enums;

enum LoanPaymentType: string
{
    case Scheduled = 'scheduled';
    case Early = 'early';
    case Penalty = 'penalty';

    public function getLabel(): string
    {
        return match ($this) {
            self::Scheduled => 'Scheduled Payment',
            self::Early => 'Early Payment',
            self::Penalty => 'Penalty Payment',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Scheduled => 'primary',
            self::Early => 'success',
            self::Penalty => 'danger',
        };
    }
}
