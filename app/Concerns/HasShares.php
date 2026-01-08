<?php

namespace App\Concerns;

use App\Models\Share;
use App\Events\Shares\SharesPurchased;
use App\Events\Shares\SharesSold;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

trait HasShares
{
    public function shares(): MorphMany
    {
        return $this->morphMany(Share::class, 'holder');
    }

    public function getApprovedSharesCount(string $currency = 'SHARE'): int
    {
        return (int) $this->shares()
            ->where('currency', $currency)
            ->where('status', \App\Enums\ShareStatus::APPROVED)
            ->sum('quantity');
    }

    public function buyShares(int $quantity, string $currency = 'SHARE'): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException("Quantity must be greater than zero.");
        }

        // Get share price from settings
        $shareSettings = app(\App\Settings\ShareSettings::class);
        $pricePerShare = $shareSettings->share_price;
        $totalCost = $quantity * $pricePerShare;

        $this->pay($totalCost, "Purchase of {$quantity} shares");

        $status = $shareSettings->require_admin_approval ? \App\Enums\ShareStatus::PENDING : \App\Enums\ShareStatus::APPROVED;

        $share = $this->shares()->create([
            'currency' => $currency,
            'quantity' => $quantity,
            'status' => $status,
            'approved_at' => $status === \App\Enums\ShareStatus::APPROVED ? now() : null,
        ]);

        event(new SharesPurchased($this, $quantity, $currency, $this->getApprovedSharesCount($currency)));
    }

    public function sellShares(int $quantity, string $currency = 'SHARE'): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException("Quantity must be greater than zero.");
        }

        $totalApproved = $this->getApprovedSharesCount($currency);

        if ($totalApproved < $quantity) {
            throw new \App\Exceptions\Shares\InsufficientSharesException("Insufficient approved shares to sell.");
        }

        // Get share price from settings
        $shareSettings = app(\App\Settings\ShareSettings::class);
        $pricePerShare = $shareSettings->share_price;
        $totalValue = $quantity * $pricePerShare;

        DB::transaction(function () use ($currency, $quantity, $totalValue) {
            $remainingToSell = $quantity;

            // FIFO: Get oldest approved shares first
            $allocations = $this->shares()
                ->where('currency', $currency)
                ->where('status', \App\Enums\ShareStatus::APPROVED)
                ->where('quantity', '>', 0)
                ->orderBy('approved_at', 'asc')
                ->get();

            foreach ($allocations as $allocation) {
                if ($remainingToSell <= 0) {
                    break;
                }

                if ($allocation->quantity <= $remainingToSell) {
                    $deduct = $allocation->quantity;
                    $remainingToSell -= $deduct;
                    $allocation->update(['quantity' => 0]);
                } else {
                    $deduct = $remainingToSell;
                    $allocation->decrement('quantity', $deduct);
                    $remainingToSell = 0;
                }
            }

            $this->deposit(\App\Enums\WalletType::MAIN, $totalValue, "Sale of {$quantity} shares");
        });

        event(new SharesSold($this, $quantity, $currency, $this->getApprovedSharesCount($currency)));
    }
}
