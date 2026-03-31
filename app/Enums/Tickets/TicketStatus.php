<?php

namespace App\Enums\Tickets;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TicketStatus: string implements HasColor, HasIcon, HasLabel
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::InProgress => 'In Progress',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Open => 'warning',
            self::InProgress => 'info',
            self::Resolved => 'success',
            self::Closed => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Open => 'heroicon-o-exclamation-circle',
            self::InProgress => 'heroicon-o-arrow-path',
            self::Resolved => 'heroicon-o-check-circle',
            self::Closed => 'heroicon-o-x-circle',
        };
    }

    public function getFluxColor(): string
    {
        return match ($this) {
            self::Open => 'yellow',
            self::InProgress => 'blue',
            self::Resolved => 'green',
            self::Closed => 'zinc',
        };
    }

    public function getFluxIcon(): string
    {
        return match ($this) {
            self::Open => 'exclamation-circle',
            self::InProgress => 'arrow-path',
            self::Resolved => 'check-circle',
            self::Closed => 'x-circle',
        };
    }

    public function isOpen(): bool
    {
        return $this === self::Open || $this === self::InProgress;
    }
}
