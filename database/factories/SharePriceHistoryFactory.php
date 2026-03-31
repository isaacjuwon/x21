<?php

namespace Database\Factories;

use App\Models\SharePriceHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SharePriceHistory>
 */
class SharePriceHistoryFactory extends Factory
{
    public function definition(): array
    {
        $oldPrice = fake()->randomFloat(2, 10, 500);

        return [
            'old_price' => $oldPrice,
            'new_price' => fake()->randomFloat(2, 10, 500),
            'created_at' => now()->subDays(fake()->numberBetween(1, 90)),
        ];
    }
}
