<?php

namespace Database\Factories;

use App\Enums\Shares\DividendStatus;
use App\Models\Dividend;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dividend>
 */
class DividendFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'percentage' => fake()->randomFloat(2, 1, 20),
            'share_price' => fake()->randomFloat(2, 10, 500),
            'total_amount' => fake()->randomFloat(2, 100, 10000),
            'status' => DividendStatus::Pending,
            'declared_at' => now(),
        ];
    }
}
