<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\LoanRepayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoanRepayment>
 */
class LoanRepaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'loan_id' => Loan::factory(),
            'amount' => fake()->randomFloat(2, 100, 5000),
            'transaction_id' => null,
        ];
    }
}
