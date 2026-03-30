<?php

namespace Database\Factories;

use App\Models\Dividend;
use App\Models\DividendPayout;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DividendPayout>
 */
class DividendPayoutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dividend_id' => Dividend::factory(),
            'user_id' => User::factory(),
            'amount' => fake()->randomFloat(2, 1, 1000),
            'transaction_id' => null,
        ];
    }
}
