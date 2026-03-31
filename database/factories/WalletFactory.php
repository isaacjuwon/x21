<?php

namespace Database\Factories;

use App\Enums\Wallets\WalletType;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wallet>
 */
class WalletFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => WalletType::General,
            'balance' => fake()->randomFloat(2, 0, 100000),
            'held_balance' => 0,
        ];
    }
}
