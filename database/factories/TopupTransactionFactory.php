<?php

namespace Database\Factories;

use App\Enums\Topups\TopupTransactionStatus;
use App\Models\TopupTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TopupTransaction>
 */
class TopupTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => fake()->randomFloat(2, 50, 5000),
            'status' => TopupTransactionStatus::Completed,
            'reference' => strtoupper(Str::random(3)).'-'.strtoupper(Str::random(10)),
            'response_message' => 'Transaction successful',
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => TopupTransactionStatus::Pending]);
    }

    public function failed(): static
    {
        return $this->state(['status' => TopupTransactionStatus::Failed]);
    }
}
