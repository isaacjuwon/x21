<?php

namespace App\Enums;

enum PropertyStatus: string
{
    case Available = 'available';
    case Sold = 'sold';
    case Pending = 'pending';
    case Rented = 'rented';
    case Archived = 'archived';
    case Draft = 'draft';

    public function getLabel(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Sold => 'Sold',
            self::Pending => 'Pending',
            self::Rented => 'Rented',
            self::Archived => 'Archived',
            self::Draft => 'Draft',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::Sold => 'danger',
            self::Pending => 'warning',
            self::Rented => 'info',
            self::Archived => 'neutral',
            self::Draft => 'neutral',
        };
    }
}
