<?php

namespace App\Enums\Webhooks;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum WebhookStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Processed = 'processed';
    case Failed = 'failed';
    case Ignored = 'ignored';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Processing => 'Processing',
            self::Processed => 'Processed',
            self::Failed => 'Failed',
            self::Ignored => 'Ignored',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Processing => 'info',
            self::Processed => 'success',
            self::Failed => 'danger',
            self::Ignored => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending => 'heroicon-o-clock',
            self::Processing => 'heroicon-o-arrow-path',
            self::Processed => 'heroicon-o-check-circle',
            self::Failed => 'heroicon-o-x-circle',
            self::Ignored => 'heroicon-o-minus-circle',
        };
    }

    public function getFluxColor(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Processing => 'blue',
            self::Processed => 'green',
            self::Failed => 'red',
            self::Ignored => 'zinc',
        };
    }

    public function getFluxIcon(): string
    {
        return match ($this) {
            self::Pending => 'clock',
            self::Processing => 'arrow-path',
            self::Processed => 'check-circle',
            self::Failed => 'x-circle',
            self::Ignored => 'minus-circle',
        };
    }
}
