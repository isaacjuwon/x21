<?php

declare(strict_types=1);

namespace App\Concerns\Wallet;

use App\Enums\WalletType;

trait GetWallets
{
    public function walletsInOrder(): array
    {
        return WalletType::getPaymentOrder();
    }

    public function getDepositableTypes(): array
    {
        return WalletType::getDepositableTypes();
    }

    public function isDepositableType(string|WalletType $type): bool
    {
        $typeValue = $type instanceof WalletType ? $type->value : $type;
        return in_array($typeValue, $this->getDepositableTypes(), true);
    }

    public function getWalletsWithBalances(): array
    {
        $wallets = [];

        foreach (WalletType::cases() as $walletType) {
            // Get or create wallet - this ensures wallet exists when accessed
            $wallet = $this->getOrCreateWallet($walletType);
            $wallets[$walletType->value] = [
                'type' => $walletType,
                'label' => $walletType->getLabel(),
                'balance' => floatval($wallet->balance),
                'formatted_balance' => number_format(floatval($wallet->balance), 2),
            ];
        }

        return $wallets;
    }
}
