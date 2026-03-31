<?php

namespace App\Jobs;

use App\Enums\Shares\DividendStatus;
use App\Enums\Wallets\WalletType;
use App\Models\Dividend;
use App\Models\DividendPayout;
use App\Models\ShareHolding;
use App\Notifications\Shares\DividendPaidNotification;
use App\Settings\ShareSettings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessDividendPayoutsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Dividend $dividend) {}

    public function handle(): void
    {
        $settings = app(ShareSettings::class);

        $eligibleHoldings = ShareHolding::query()
            ->where('quantity', '>', 0)
            ->where('acquired_at', '<=', now()->subDays($settings->holding_period_days))
            ->with('user')
            ->get();

        if ($eligibleHoldings->isEmpty()) {
            $this->dividend->update([
                'status' => DividendStatus::Distributed,
                'total_amount' => 0,
            ]);

            return;
        }

        $dividendPerShare = $this->dividend->share_price * ($this->dividend->percentage / 100);
        $totalDistributedAmount = 0;

        foreach ($eligibleHoldings as $holding) {
            $payoutAmount = round($dividendPerShare * $holding->quantity, 2);

            if ($payoutAmount <= 0) {
                continue;
            }

            $transaction = $holding->user->deposit($payoutAmount, WalletType::General);

            $payout = DividendPayout::create([
                'dividend_id' => $this->dividend->id,
                'user_id' => $holding->user->id,
                'amount' => $payoutAmount,
                'transaction_id' => $transaction->id,
            ]);

            $holding->user->notify(new DividendPaidNotification($payout));

            $totalDistributedAmount += $payoutAmount;
        }

        $this->dividend->update([
            'status' => DividendStatus::Distributed,
            'total_amount' => $totalDistributedAmount,
        ]);
    }
}
