<?php

namespace App\Jobs;

use App\Enums\Shares\DividendStatus;
use App\Enums\Wallets\WalletType;
use App\Models\Dividend;
use App\Models\DividendPayout;
use App\Models\ShareHolding;
use App\Notifications\Shares\DividendPaidNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessDividendPayoutsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Dividend $dividend) {}

    public function handle(): void
    {
        $eligibleHoldings = ShareHolding::query()
            ->where('quantity', '>', 0)
            ->where('acquired_at', '<=', now()->subDays(config('shares.holding_period_days')))
            ->with('user')
            ->get();

        $totalEligibleShares = $eligibleHoldings->sum('quantity');

        if ($totalEligibleShares === 0) {
            $this->dividend->update(['status' => DividendStatus::Distributed]);

            return;
        }

        foreach ($eligibleHoldings as $holding) {
            $payoutAmount = round(($holding->quantity / $totalEligibleShares) * $this->dividend->total_amount, 2);

            $transaction = $holding->user->deposit($payoutAmount, WalletType::General);

            $payout = DividendPayout::create([
                'dividend_id' => $this->dividend->id,
                'user_id' => $holding->user->id,
                'amount' => $payoutAmount,
                'transaction_id' => $transaction->id,
            ]);

            $holding->user->notify(new DividendPaidNotification($payout));
        }

        $this->dividend->update(['status' => DividendStatus::Distributed]);
    }
}
