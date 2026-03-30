<?php

namespace Database\Factories;

use App\Models\ShareListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShareListing>
 */
class ShareListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'price' => fake()->randomFloat(2, 10, 500),
            'total_shares' => 1000,
            'available_shares' => 1000,
        ];
    }
}
