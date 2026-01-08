<?php

// app/Models/Dividend.php

namespace App\Models;

use App\Events\Dividend\DividendPaid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Dividend extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency',
        'amount_per_share',
        'type',
        'declaration_date',
        'ex_dividend_date',
        'record_date',
        'payment_date',
        'paid_out',
        'metadata',
    ];

    protected $casts = [
        'amount_per_share' => 'decimal:6',
        'declaration_date' => 'date',
        'ex_dividend_date' => 'date',
        'record_date' => 'date',
        'payment_date' => 'date',
        'paid_out' => 'boolean',
        'metadata' => 'array',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(DividendPayment::class);
    }

    public function processPayout(): void
    {
        if ($this->paid_out) {
            return;
        }

        DB::transaction(function () {
            $shareSettings = app(\App\Settings\ShareSettings::class);
            $holdingPeriodDays = $shareSettings->holding_period;
            
            // Only shares approved before (record_date - holding_period) are eligible
            $eligibilityDate = $this->record_date->subDays($holdingPeriodDays);

            // Get all qualified shareholders grouped by holder to ensure a single payout per user
            $eligibleShares = Share::where('currency', $this->currency)
                ->where('status', \App\Enums\ShareStatus::APPROVED)
                ->where('quantity', '>', 0)
                ->where('approved_at', '<=', $eligibilityDate)
                ->with('holder')
                ->get()
                ->groupBy(fn ($share) => $share->holder_type . ':' . $share->holder_id);

            if ($eligibleShares->isEmpty()) {
                // No shareholders to pay
                $this->update(['paid_out' => true]);

                return;
            }

            foreach ($eligibleShares as $shares) {
                // Aggregate shares for this holder
                $firstShare = $shares->first();
                $totalQuantity = $shares->sum('quantity');
                
                // We'll pass a dummy share object or modify processDividendForShareholder to accept holder and quantity
                $this->processDividendForHolder($firstShare->holder, $totalQuantity);
            }

            $this->update(['paid_out' => true]);
        });
    }

    public function processDividendForHolder($holder, int $quantity): void
    {
        $amount = $quantity * $this->amount_per_share;

        $payment = $this->payments()->create([
            'holder_type' => get_class($holder),
            'holder_id' => $holder->id,
            'shares_held' => $quantity,
            'amount_per_share' => $this->amount_per_share,
            'total_amount' => $amount,
            'paid_at' => now(),
        ]);

        $holder->deposit(\App\Enums\WalletType::BONUS, $amount, "Dividend payout for {$quantity} shares");

        \App\Events\Dividend\DividendPaid::dispatch($holder, $this, $payment);
    }

    // Method to process dividends in chunks for large datasets
    public function processPayoutInChunks(int $chunkSize = 1000): void
    {
        if ($this->paid_out) {
            return;
        }

        DB::transaction(function () use ($chunkSize) {
            $shareSettings = app(\App\Settings\ShareSettings::class);
            $holdingPeriodDays = $shareSettings->holding_period;
            $eligibilityDate = $this->record_date->subDays($holdingPeriodDays);

            Share::select('holder_type', 'holder_id', DB::raw('SUM(quantity) as total_quantity'))
                ->where('currency', $this->currency)
                ->where('status', \App\Enums\ShareStatus::APPROVED)
                ->where('quantity', '>', 0)
                ->where('approved_at', '<=', $eligibilityDate)
                ->groupBy('holder_type', 'holder_id')
                ->chunk($chunkSize, function ($results) {
                    foreach ($results as $result) {
                        $this->processDividendForHolder($result->holder, $result->total_quantity);
                    }
                });

            $this->update(['paid_out' => true]);
        });
    }

    // Get statistics about the dividend payout
    public function getPayoutStatistics(): array
    {
        return [
            'total_shareholders' => $this->payments()->count(),
            'total_shares' => $this->payments()->sum('shares_held'),
            'total_payout' => $this->payments()->sum('total_amount'),
            'average_per_shareholder' => $this->payments()->avg('total_amount'),
        ];
    }
}
