<?php

namespace App\Events\Wallets;

use App\Models\Transaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Transaction $transaction,
        public string $reason,
    ) {}
}
