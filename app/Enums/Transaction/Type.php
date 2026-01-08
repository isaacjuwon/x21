<?php

namespace App\Enums\Transaction;

enum Type: string
{
    case Payment = 'payment';
    case Deposit = 'deposit';
    case Refund = 'refund';
    case Withdrawal = 'withdrawal';

    public function getLabel(): string
    {
        return match ($this) {
            self::Payment => 'Payment',
            self::Deposit => 'Deposit',
            self::Refund => 'Refund',
            self::Withdrawal => 'Withdrawal',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Payment => 'red',
            self::Deposit => 'green',
            self::Refund => 'blue',
            self::Withdrawal => 'orange',
        };
    }

    public static function match(string|null $value): Type
    {
        return match ($value) {
            Type::Deposit->value => Type::Deposit,
            Type::Refund->value => Type::Refund,
            Type::Withdrawal->value => Type::Withdrawal,
            Type::Payment->value => Type::Payment,
            default => Type::Payment,
        };
    }

}
