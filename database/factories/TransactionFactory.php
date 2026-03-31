<?php

namespace Database\Factories;

use App\Enums\Wallets\TransactionStatus;
use App\Enums\Wallets\TransactionType;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(TransactionType::cases());

        return [
            'wallet_id' => Wallet::factory(),
            'amount' => fake()->randomFloat(2, 10, 10000),
            'type' => $type,
            'status' => TransactionStatus::Completed,
            'reference' => strtoupper(Str::random(3)).'-'.strtoupper(Str::random(10)),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function deposit(): static
    {
        return $this->state(['type' => TransactionType::Deposit, 'status' => TransactionStatus::Completed]);
    }

    public function withdrawal(): static
    {
        return $this->state(['type' => TransactionType::Withdrawal, 'status' => TransactionStatus::Completed]);
    }

    public function pending(): static
    {
        return $this->state(['status' => TransactionStatus::Pending]);
    }
}
