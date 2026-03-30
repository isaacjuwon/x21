<?php

namespace Database\Factories;

use App\Enums\Loans\LoanScheduleEntryStatus;
use App\Models\Loan;
use App\Models\LoanScheduleEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoanScheduleEntry>
 */
class LoanScheduleEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $instalmentAmount = fake()->randomFloat(2, 100, 2000);
        $principalComponent = fake()->randomFloat(2, 50, $instalmentAmount - 1);
        $interestComponent = round($instalmentAmount - $principalComponent, 2);

        return [
            'loan_id' => Loan::factory(),
            'instalment_number' => fake()->numberBetween(1, 60),
            'due_date' => fake()->dateTimeBetween('now', '+5 years'),
            'instalment_amount' => $instalmentAmount,
            'principal_component' => $principalComponent,
            'interest_component' => $interestComponent,
            'outstanding_balance' => fake()->randomFloat(2, 0, 50000),
            'status' => LoanScheduleEntryStatus::Pending,
            'remaining_amount' => $instalmentAmount,
        ];
    }
}
