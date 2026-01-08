<?php

declare(strict_types=1);

namespace App\Concerns\Wallet;

use App\Enums\WalletType;
use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Exceptions\Wallet\InvalidWalletTypeException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait HandlesTransfer
{
    /**
     * Transfer funds to another user's wallet of a specific type.
     *
     * @param  Model  $recipient  The recipient of the transfer.
     * @param  float  $amount  The amount to transfer.
     * @param  string|WalletType  $type  The type of wallet to transfer from/to.
     * @param  string|null  $notes  Optional notes for the transaction.
     *
     * @throws InsufficientBalanceException
     * @throws InvalidWalletTypeException
     * @throws \InvalidArgumentException
     */
    public function transfer(Model $recipient, float $amount, string|WalletType $type, ?string $notes = null): bool
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Transfer amount must be greater than zero.');
        }

        if ($this->is($recipient)) {
            throw new \InvalidArgumentException('Cannot transfer to the same wallet owner.');
        }

        $walletType = $type instanceof WalletType ? $type : WalletType::tryFrom($type);

        if (! $walletType) {
            throw new InvalidWalletTypeException("Invalid wallet type '{$type}'.");
        }

        $senderWallet = $this->getOrCreateWallet($walletType);

        if (! $senderWallet->hasSufficientBalance($amount)) {
            throw new InsufficientBalanceException("Insufficient balance in your {$walletType->getLabel()} to complete the transfer.");
        }

        return DB::transaction(function () use ($recipient, $senderWallet, $amount, $walletType, $notes) {
            // Decrement sender's wallet
            $senderNote = "Transfer of {$amount} to user ID {$recipient->id}. ".($notes ?? '');
            $senderWallet->decrementAndCreateLog($amount, trim($senderNote));

            // Increment recipient's wallet
            $recipientWallet = $recipient->getOrCreateWallet($walletType);
            $recipientNote = "Transfer of {$amount} from user ID {$this->id}. ".($notes ?? '');
            $recipientWallet->incrementAndCreateLog($amount, trim($recipientNote));

            return true;
        });

        if ($result) {
            event(new \App\Events\Wallet\WalletTransferred($this, $recipient, $amount, $walletType, $notes));
        }

        return $result;
    }
}
