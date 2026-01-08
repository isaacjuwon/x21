<?php

declare(strict_types=1);

namespace App\Concerns\Wallet;

use App\Enums\WalletType;
use App\Exceptions\Wallet\InsufficientBalanceException;
use Illuminate\Support\Facades\DB;

trait HandlesPayment
{
    /**
     * Pay the order value from the user's wallets.
     *
     * @throws InsufficientBalanceException
     */
    public function pay(float $orderValue, ?string $notes = null): void
    {
        if ($orderValue <= 0) {
            throw new \InvalidArgumentException('Payment amount must be greater than zero.');
        }

        if (!$this->hasSufficientBalance($orderValue)) {
            throw new InsufficientBalanceException('Insufficient balance for this payment.');
        }

        $deductions = [];

        DB::transaction(function () use ($orderValue, $notes, &$deductions) {
            $remainingAmount = $orderValue;

            foreach ($this->walletsInOrder() as $walletType) {
                if ($remainingAmount <= 0) {
                    break;
                }

                $walletEnumType = WalletType::tryFrom($walletType);
                $wallet = $this->wallets()->where('type', $walletEnumType)->first();

                if (!$wallet || $wallet->balance <= 0) {
                    continue;
                }

                $deductAmount = min($remainingAmount, $wallet->balance);
                $wallet->decrementAndCreateLog($deductAmount, $notes);
                $remainingAmount -= $deductAmount;

                $deductions[] = [
                    'type' => $walletEnumType,
                    'amount' => $deductAmount,
                ];
            }

            if ($remainingAmount > 0) {
                throw new InsufficientBalanceException('Unable to complete payment due to insufficient balance.');
            }
        });

        foreach ($deductions as $deduction) {
            event(new \App\Events\Wallet\WalletDebited($this, $deduction['amount'], $deduction['type'], $notes));
        }
    }

    /**
     * Pay from a specific wallet type
     */
    public function payFromWallet(string|WalletType $type, float $amount, ?string $notes = null): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Payment amount must be greater than zero.');
        }

        $typeValue = $type instanceof WalletType ? $type->value : $type;
        $walletType = $type instanceof WalletType ? $type : WalletType::tryFrom($typeValue);

        if (!$walletType) {
            throw new \InvalidArgumentException("Invalid wallet type '{$typeValue}'.");
        }

        $wallet = $this->wallets()->where('type', $walletType)->first();

        if (!$wallet) {
            throw new InsufficientBalanceException("Wallet of type '{$typeValue}' not found.");
        }

        if (!$wallet->hasSufficientBalance($amount)) {
            throw new InsufficientBalanceException("Insufficient balance in {$walletType->getLabel()}.");
        }

        DB::transaction(function () use ($wallet, $amount, $notes) {
            $wallet->decrementAndCreateLog($amount, $notes);
        });

        event(new \App\Events\Wallet\WalletDebited($this, $amount, $walletType, $notes));
    }

    /**
     * Check if user can afford a payment
     */
    public function canAfford(float $amount): bool
    {
        return $this->hasSufficientBalance($amount);
    }
}
