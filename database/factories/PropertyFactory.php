<?php

namespace Database\Factories;

use App\Enums\PropertyListingType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'title' => $title,
            'description' => fake()->paragraphs(3, true),
            'type' => fake()->randomElement(PropertyType::cases()),
            'status' => fake()->randomElement(PropertyStatus::cases()),
            'listing_type' => fake()->randomElement(PropertyListingType::cases()),
            'price' => fake()->randomFloat(2, 50000, 1000000),
            'price_per_sqft' => fake()->randomFloat(2, 100, 1000),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country' => 'Nigeria',
            'postal_code' => fake()->postcode(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'bedrooms' => fake()->numberBetween(1, 6),
            'bathrooms' => fake()->numberBetween(1, 5),
            'area_sqft' => fake()->numberBetween(500, 5000),
            'year_built' => fake()->year(),
            'has_garage' => fake()->boolean(),
            'is_furnished' => fake()->boolean(),
            'parking_spaces' => fake()->numberBetween(0, 4),
            'features' => fake()->randomElements(['Pool', 'Garden', 'Security', 'Wi-Fi', 'Gym', 'Elevator'], fake()->numberBetween(2, 4)),
            'images' => [fake()->imageUrl()],
            'slug' => Str::slug($title).'-'.uniqid(),
            'meta_title' => $title,
            'meta_description' => fake()->sentence(),
            'is_featured' => fake()->boolean(20),
            'is_active' => true,
            'owner_name' => fake()->name(),
            'owner_email' => fake()->safeEmail(),
            'owner_phone' => fake()->phoneNumber(),
        ];
    }
}
