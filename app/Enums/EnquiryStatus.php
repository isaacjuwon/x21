<?php

namespace App\Enums;

enum EnquiryStatus: string
{
    case Pending = 'pending';
    case Responded = 'responded';
    case Closed = 'closed';
    case Spam = 'spam';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Responded => 'Responded',
            self::Closed => 'Closed',
            self::Spam => 'Spam',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Responded => 'info',
            self::Closed => 'success',
            self::Spam => 'danger',
        };
    }
}
