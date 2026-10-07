<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Plans\ServiceType;
use App\Models\Brand;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'brand_id' => Brand::factory(),
            'service_type' => fake()->randomElement(ServiceType::cases()),
            'name' => fake()->words(3, true),
            'type' => fake()->randomElement(['SME', 'GIFTING', 'CORPORATE', null]),
            'api_code' => strtoupper(fake()->bothify('PLAN_###')),
            'price' => fake()->randomFloat(2, 100, 5000),
            'cost_price' => fake()->randomFloat(2, 80, 4500),
            'duration' => '30 Days',
            'status' => true,
            'meta' => null,
        ];
    }

    public function forService(ServiceType|string $serviceType): static
    {
        return $this->state(fn () => [
            'service_type' => $serviceType instanceof ServiceType ? $serviceType : ServiceType::from($serviceType),
        ]);
    }

    public function airtime(): static
    {
        return $this->forService(ServiceType::Airtime)->state(fn () => [
            'price' => 0.00,
            'duration' => null,
            'type' => 'VTU',
        ]);
    }

    public function data(): static
    {
        return $this->forService(ServiceType::Data);
    }

    public function cable(): static
    {
        return $this->forService(ServiceType::Cable);
    }

    public function electricity(): static
    {
        return $this->forService(ServiceType::Electricity)->state(fn () => [
            'duration' => null,
            'type' => fake()->randomElement(['Prepaid', 'Postpaid']),
        ]);
    }

    public function education(): static
    {
        return $this->forService(ServiceType::Education);
    }

    public function exam(): static
    {
        return $this->forService(ServiceType::Exam);
    }
}
