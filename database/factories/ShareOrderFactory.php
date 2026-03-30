<?php

namespace Database\Factories;

use App\Enums\Shares\ShareOrderStatus;
use App\Enums\Shares\ShareOrderType;
use App\Models\ShareOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShareOrder>
 */
class ShareOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 100);
        $pricePerShare = fake()->randomFloat(2, 10, 500);

        return [
            'user_id' => User::factory(),
            'type' => ShareOrderType::Buy,
            'quantity' => $quantity,
            'price_per_share' => $pricePerShare,
            'total_amount' => round($quantity * $pricePerShare, 2),
            'status' => ShareOrderStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(['status' => ShareOrderStatus::Approved]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status' => ShareOrderStatus::Rejected,
            'rejection_reason' => fake()->sentence(),
        ]);
    }

    public function buy(): static
    {
        return $this->state(['type' => ShareOrderType::Buy]);
    }

    public function sell(): static
    {
        return $this->state(['type' => ShareOrderType::Sell]);
    }
}
