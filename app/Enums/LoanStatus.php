<?php

namespace App\Enums;

enum LoanStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case DISBURSED = 'disbursed';
    case ACTIVE = 'active';
    case FULLY_PAID = 'fully_paid';
    case DEFAULTED = 'defaulted';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::DISBURSED => 'Disbursed',
            self::ACTIVE => 'Active',
            self::FULLY_PAID => 'Fully Paid',
            self::DEFAULTED => 'Defaulted',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::APPROVED => 'info',
            self::REJECTED => 'danger',
            self::DISBURSED => 'primary',
            self::ACTIVE => 'primary',
            self::FULLY_PAID => 'success',
            self::DEFAULTED => 'danger',
        };
    }

    public function getBadgeColor(): string
    {
        return $this->getColor();
    }
}
