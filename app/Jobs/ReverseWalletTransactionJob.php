<?php

namespace App\Jobs;

use App\Actions\Wallets\ReverseWalletTransactionAction;
use App\Models\Transaction;
use App\Notifications\Wallets\TransactionReversedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReverseWalletTransactionJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public Transaction $transaction,
        public string $reason,
    ) {}

    public function handle(ReverseWalletTransactionAction $action): void
    {
        $refund = $action->handle($this->transaction, $this->reason);

        // Notify the user their wallet has been refunded
        $this->transaction->wallet->user->notify(
            new TransactionReversedNotification($this->transaction, $refund)
        );

        Log::info("Wallet reversal completed for transaction #{$this->transaction->reference}", [
            'refund_reference' => $refund->reference,
            'amount' => $this->transaction->amount,
            'user_id' => $this->transaction->wallet->user_id,
        ]);
    }

    public function failed(Throwable $e): void
    {
        Log::error("Wallet reversal FAILED for transaction #{$this->transaction->reference}: {$e->getMessage()}", [
            'transaction_id' => $this->transaction->id,
            'reason' => $this->reason,
        ]);
    }
}
