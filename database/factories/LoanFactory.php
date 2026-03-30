<?php

namespace Database\Factories;

use App\Enums\Loans\InterestMethod;
use App\Enums\Loans\LoanStatus;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Loan>
 */
class LoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $principal = fake()->randomFloat(2, 1000, 50000);

        return [
            'user_id' => User::factory(),
            'principal_amount' => $principal,
            'outstanding_balance' => $principal,
            'interest_rate' => fake()->randomFloat(4, 0.05, 0.30),
            'repayment_term_months' => fake()->numberBetween(3, 60),
            'interest_method' => InterestMethod::FlatRate,
            'status' => LoanStatus::Active,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LoanStatus::Approved,
        ]);
    }

    public function disbursed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LoanStatus::Disbursed,
            'disbursed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LoanStatus::Rejected,
            'rejection_reason' => fake()->sentence(),
        ]);
    }
}
