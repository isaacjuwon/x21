<?php

declare(strict_types=1);

namespace App\Concerns\Wallet;

use App\Enums\WalletType;
use App\Exceptions\Wallet\InvalidWalletTypeException;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasWallet
{
    use GetWallets;

    public function wallets(): MorphMany
    {
        return $this->morphMany(Wallet::class, 'owner');
    }

    public function getWalletBalanceAttribute(): float
    {
        $totalBalance = 0.0;

        foreach (WalletType::getPaymentOrder() as $walletType) {
            $walletEnumType = WalletType::tryFrom($walletType);
            if ($walletEnumType) {
                // Get or create wallet - this ensures wallet exists when calculating balance
                $wallet = $this->getOrCreateWallet($walletEnumType);
                $totalBalance += $wallet->balance;
            }
        }

        return $totalBalance;
    }

    public function hasSufficientBalance(float $value): bool
    {
        return $this->walletBalance >= $value;
    }

    public function getWalletBalanceByType(string|WalletType $walletType): float
    {
        $walletTypeValue = $walletType instanceof WalletType ? $walletType->value : $walletType;
        $walletEnumType = $walletType instanceof WalletType ? $walletType : WalletType::tryFrom($walletTypeValue);

        if (! $walletEnumType) {
            throw new InvalidWalletTypeException("Invalid wallet type '{$walletTypeValue}'.");
        }

        // Get or create wallet - this ensures wallet exists when checking balance
        $wallet = $this->getOrCreateWallet($walletEnumType);

        return (float) $wallet->balance;
    }

    public function getWalletBalance(): float
    {
        return $this->walletBalance;
    }

    public function getOrCreateWallet(WalletType $type): Wallet
    {
        return $this->wallets()->firstOrCreate(
            ['type' => $type],
            ['balance' => 0.0]
        );
    }

    public function getFormattedWalletBalanceAttribute(): string
    {
        return number_format($this->walletBalance, 2);
    }
}
