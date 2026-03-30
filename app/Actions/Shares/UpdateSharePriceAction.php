<?php

namespace App\Actions\Shares;

use App\Models\ShareListing;
use App\Models\SharePriceHistory;

class UpdateSharePriceAction
{
    public function handle(float $newPrice): ShareListing
    {
        $listing = ShareListing::firstOrFail();

        SharePriceHistory::create([
            'old_price' => $listing->price,
            'new_price' => $newPrice,
            'created_at' => now(),
        ]);

        $listing->update(['price' => $newPrice]);

        return $listing->fresh();
    }
}
