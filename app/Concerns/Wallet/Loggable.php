<?php

declare(strict_types=1);

namespace App\Concerns\Wallet;

use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Loggable
{
    public function transactions(): MorphMany
    {
        return $this->morphMany(WalletTransaction::class, 'loggable')
            ->where('wallet_type', $this->type)
            ->orderBy('created_at', 'desc');
    }

    public function successfulTransactions(): MorphMany
    {
        return $this->transactions()->where('status', 'success');
    }

    public function recentTransactions(int $limit = 10): MorphMany
    {
        return $this->transactions()->limit($limit);
    }

    public function transactionsByType(string $type): MorphMany
    {
        return $this->transactions()->where('transaction_type', $type);
    }

    public function getTotalDepositedAttribute(): float
    {
        return $this->successfulTransactions()
            ->where('transaction_type', 'increment')
            ->sum('amount');
    }

    public function getTotalWithdrawnAttribute(): float
    {
        return $this->successfulTransactions()
            ->where('transaction_type', 'decrement')
            ->sum('amount');
    }
}
