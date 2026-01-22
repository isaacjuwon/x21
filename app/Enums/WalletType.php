<?php

declare(strict_types=1);

namespace App\Enums;

enum WalletType: string
{
    case Main = 'main';
    case Bonus = 'bonus';

    /**
     * Check if a given value is a valid enum case.
     */
    public static function isValid(string $type): bool
    {
        return collect(self::cases())
            ->pluck('value')
            ->contains($type);
    }

    /**
     * Get all wallet types as array for forms/dropdowns
     */
    public static function toArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->getLabel()])
            ->toArray();
    }

    /**
     * Get human-readable label for the wallet type
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::Main => 'Main Wallet',
            self::Bonus => 'Bonus Wallet',
        };
    }

    /**
     * Get wallet types that can receive deposits
     */
    public static function getDepositableTypes(): array
    {
        return [
            self::Main->value,
            self::Bonus->value,
        ];
    }

    /**
     * Get wallet types in priority order for payments
     */
    public static function getPaymentOrder(): array
    {
        return [
            self::Main->value,
            self::Bonus->value,
        ];
    }
}
