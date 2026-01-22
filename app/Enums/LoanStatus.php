<?php

namespace App\Enums;

enum LoanStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Disbursed = 'disbursed';
    case Active = 'active';
    case FullyPaid = 'fully_paid';
    case Defaulted = 'defaulted';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Disbursed => 'Disbursed',
            self::Active => 'Active',
            self::FullyPaid => 'Fully Paid',
            self::Defaulted => 'Defaulted',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'info',
            self::Rejected => 'danger',
            self::Disbursed => 'primary',
            self::Active => 'primary',
            self::FullyPaid => 'success',
            self::Defaulted => 'danger',
        };
    }

    public function getBadgeColor(): string
    {
        return $this->getColor();
    }
}
