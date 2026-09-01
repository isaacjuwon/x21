<?php

namespace Database\Factories;

use App\Enums\Banners\BannerLocation;
use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'location' => fake()->randomElement(BannerLocation::cases()),
            'content' => '<p>'.fake()->paragraph().'</p>',
            'link_url' => fake()->optional()->url(),
            'link_text' => fake()->optional()->word(),
            'starts_at' => null,
            'ends_at' => null,
            'sort_order' => fake()->numberBetween(0, 10),
            'is_active' => true,
            'is_dismissible' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function nonDismissible(): static
    {
        return $this->state(['is_dismissible' => false]);
    }
}
