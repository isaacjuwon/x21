<?php

namespace Database\Factories;

use App\Models\ShareHolding;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShareHolding>
 */
class ShareHoldingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'quantity' => fake()->numberBetween(1, 1000),
            'acquired_at' => now()->subDays(60),
        ];
    }
}
