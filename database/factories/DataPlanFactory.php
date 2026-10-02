<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\DataPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DataPlan>
 */
class DataPlanFactory extends Factory
{
    public function definition(): array
    {
        $mb = fake()->randomElement([500, 1000, 2000, 3000, 5000]);

        return [
            'name' => $mb.'MB Data Plan',
            'brand_id' => Brand::factory(),
            'type' => fake()->randomElement(['SME', 'GIFTING', 'CORPORATE', 'DIRECT']),
            'api_code' => '',
            'vtpass_code' => null,
            'price' => fake()->randomFloat(2, 100, 5000),
            'duration' => fake()->randomElement(['7 Days', '30 Days', '1 Month', '90 Days']),
            'status' => true,
        ];
    }
}
