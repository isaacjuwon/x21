<?php

declare(strict_types=1);

namespace App\Concerns\Wallet;

use App\Enums\WalletType;
use App\Exceptions\Wallet\InvalidDepositException;
use App\Exceptions\Wallet\InvalidValueException;
use App\Exceptions\Wallet\InvalidWalletTypeException;
use Illuminate\Support\Facades\DB;

trait HandlesDeposit
{
    /**
     * Deposit an amount to the user's wallet of a specific type.
     *
     * @throws InvalidDepositException
     * @throws InvalidValueException
     * @throws InvalidWalletTypeException
     */
    public function deposit(string|WalletType $type, float $amount, ?string $notes = null): bool
    {
        $settings = app(\App\Settings\WalletSettings::class);
        if (! $settings->wallet_enabled) {
            throw new \RuntimeException('Wallet is currently disabled.');
        }

        if ($amount < $settings->min_funding_amount) {
            throw new InvalidValueException("Minimum deposit amount is {$settings->minimum_deposit}.");
        }

        if ($amount > $settings->max_funding_amount) {
            throw new InvalidValueException("Maximum deposit amount is {$settings->maximum_deposit}.");
        }

        $typeValue = $type instanceof WalletType ? $type->value : $type;
        $walletType = $type instanceof WalletType ? $type : WalletType::tryFrom($typeValue);

        if (! $walletType) {
            throw new InvalidWalletTypeException("Invalid wallet type '{$typeValue}'.");
        }

        $depositable = $this->getDepositableTypes();

        if (! in_array($typeValue, $depositable, true)) {
            throw new InvalidDepositException('Invalid deposit request. Wallet type is not depositable.');
        }

        if ($amount <= 0) {
            throw new InvalidValueException('Deposit amount must be greater than zero.');
        }

        $result = DB::transaction(function () use ($walletType, $amount, $notes) {
            $wallet = $this->wallets()->firstOrCreate(['type' => $walletType]);
            $wallet->incrementAndCreateLog($amount, $notes);

            return true;
        });

        if ($result) {
            event(new \App\Events\Wallet\WalletCredited($this, $amount, $walletType, $notes));
        }

        return $result;
    }

    /**
     * Check if deposit is valid for the given type
     */
    private function isRequestValid(string $type, array $depositable): bool
    {
        return in_array($type, $depositable, true) && WalletType::isValid($type);
    }

    /**
     * Bulk deposit to multiple wallets
     */
    public function bulkDeposit(array $deposits, ?string $notes = null): bool
    {
        return DB::transaction(function () use ($deposits, $notes) {
            foreach ($deposits as $deposit) {
                $type = $deposit['type'] ?? null;
                $amount = $deposit['amount'] ?? 0;
                $depositNotes = $deposit['notes'] ?? $notes;

                if ($type && $amount > 0) {
                    $this->deposit($type, $amount, $depositNotes);
                }
            }

            return true;
        });
    }
}
