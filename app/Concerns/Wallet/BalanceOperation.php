<?php

declare(strict_types=1);

namespace App\Concerns\Wallet;

use App\Events\Wallet\WalletBalanceUpdated;
use App\Events\Wallet\WalletTransactionCreated;
use App\Models\WalletTransaction;
use InvalidArgumentException;

trait BalanceOperation
{
    protected WalletTransaction $createdTransaction;

    /**
     * Check if Balance is more than zero.
     */
    public function hasBalance(): bool
    {
        return $this->balance > 0;
    }

    /**
     * Decrement Balance and create a transaction log entry.
     */
    public function decrementAndCreateLog(float $value, ?string $notes = null): void
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('Value must be greater than zero');
        }

        $previousBalance = $this->balance;
        $this->createTransactionLog('decrement', $value, $notes);
        $this->decrement('balance', $value);

       // WalletBalanceUpdated::dispatch($this, $previousBalance, $this->fresh()->balance);
    }

    /**
     * Increment Balance and create a transaction log entry.
     */
    public function incrementAndCreateLog(float $value, ?string $notes = null): void
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('Value must be greater than zero');
        }

        $previousBalance = $this->balance;
        $this->createTransactionLog('increment', $value, $notes);
        $this->increment('balance', $value);

       // WalletBalanceUpdated::dispatch($this, $previousBalance, $this->fresh()->balance);
    }

    /**
     * Create a transaction log entry.
     */
    private function createTransactionLog(string $type, float $value, ?string $notes = null): void
    {
        $fromBalance = $this->balance;
        $toBalance = $type === 'increment' ? $fromBalance + $value : $fromBalance - $value;

        $this->createdTransaction = WalletTransaction::create([
            'loggable_type' => $this->owner_type,
            'loggable_id' => $this->owner_id,
            'status' => 'success',
            'from_balance' => $fromBalance,
            'to_balance' => $toBalance,
            'wallet_type' => $this->type,
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'amount' => $value,
            'notes' => $notes,
            'reference' => $this->generateReference(),
            'transaction_type' => $type,
        ]);

        // WalletTransactionCreated::dispatch($this->createdTransaction);
    }

    /**
     * Generate a unique reference for the transaction.
     */
    private function generateReference(): string
    {
        return config('wallet.log_reference_prefix', '') .
            \Illuminate\Support\Str::random(config('wallet.log_reference_length', 12));
    }

    /**
     * Get the created transaction log.
     */
    public function getCreatedTransaction(): ?WalletTransaction
    {
        return $this->createdTransaction ?? null;
    }
}
