<?php

declare(strict_types=1);

namespace App\Actions\Share;

use App\Enums\ShareStatus;
use App\Http\Payloads\Share\SellSharePayload;
use App\Models\Share;
use App\Models\User;
use App\Settings\ShareSettings;
use Exception;
use Illuminate\Support\Facades\DB;

class SellShareAction
{
    public function __construct(
        protected ShareSettings $settings
    ) {}

    public function handle(User $user, SellSharePayload $payload): void
    {
        DB::transaction(function () use ($user, $payload) {
            $quantityToSell = $payload->quantity;

            // Find approved shares for this user, ordered by oldest first (FIFO)
            $shares = Share::where('holder_type', User::class)
                ->where('holder_id', $user->id)
                ->where('status', ShareStatus::APPROVED)
                ->orderBy('created_at', 'asc')
                ->lockForUpdate() // Lock rows to prevent race conditions
                ->get();

            $totalAvailable = $shares->sum('quantity');

            if ($totalAvailable < $quantityToSell) {
                throw new Exception("Insufficient shares to sell. Available: {$totalAvailable}, Required: {$quantityToSell}");
            }

            $remainingToSell = $quantityToSell;

            foreach ($shares as $share) {
                if ($remainingToSell <= 0) {
                    break;
                }

                if ($share->quantity <= $remainingToSell) {
                    // Fully liquidate this share record
                    $remainingToSell -= $share->quantity;
                    $share->delete(); // Or update status to LIQUIDATED if soft delete/status tracking is preferred. Migration suggests simple records.
                } else {
                    // Partially liquidate
                    $share->decrement('quantity', $remainingToSell);
                    $remainingToSell = 0;
                }
            }

            // Credit user wallet
            $pricePerShare = $this->settings->share_price;
            $totalCredit = $pricePerShare * $quantityToSell;

            $user->increment('balance', $totalCredit);
        });
    }
}
