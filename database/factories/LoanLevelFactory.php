<?php

namespace Database\Factories;

use App\Models\LoanLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoanLevel>
 */
class LoanLevelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'max_amount' => fake()->randomFloat(2, 5000, 100000),
            'min_amount' => fake()->randomFloat(2, 500, 4999),
            'interest_rate' => fake()->randomFloat(2, 5, 25),
            'max_term_months' => fake()->randomElement([12, 24, 36, 48, 60]),
            'is_active' => true,
        ];
    }
}
