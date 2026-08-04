<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PetFriendlyPlace>
 */
class PetFriendlyPlaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $lat = fake()->latitude(-34.90, 32.70);
        $lng = fake()->longitude(-73.99, -34.70);

        return [
            'name' => ucfirst(fake()->words(2, true)),
            'category' => fake()->randomElement(['pipican', 'beach', 'park', 'vet', 'shop', 'restaurant', 'hotel']),
            'description' => fake()->optional()->paragraph(),
            'lat' => $lat,
            'lng' => $lng,
            'address' => fake()->optional()->streetAddress(),
            'phone' => fake()->optional()->phoneNumber(),
            'website' => fake()->optional()->url(),
            'photo' => fake()->optional()->randomElement([
                'places/sample-1.jpg',
                'places/sample-2.jpg',
                'places/sample-3.jpg',
            ]),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
            'created_by' => User::factory(),
        ];
    }
}
