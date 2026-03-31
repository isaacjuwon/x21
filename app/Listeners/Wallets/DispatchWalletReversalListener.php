<?php

namespace App\Listeners\Wallets;

use App\Events\Wallets\TransactionFailed;
use App\Jobs\ReverseWalletTransactionJob;

class DispatchWalletReversalListener
{
    public function handle(TransactionFailed $event): void
    {
        // Only reverse withdrawals — deposits and holds don't need reversal
        if (! $event->transaction->type->isWithdrawal()) {
            return;
        }

        ReverseWalletTransactionJob::dispatch($event->transaction, $event->reason)
            ->onQueue('wallet-reversals');
    }
}
