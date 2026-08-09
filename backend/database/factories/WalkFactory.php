<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Walk>
 */
class WalkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'creator_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'lat' => fake()->latitude(-34.8, 28.2),
            'lng' => fake()->longitude(-73.9, -34.7),
            'scheduled_at' => fake()->dateTimeBetween('+1 hour', '+14 days'),
            'max_pets' => fake()->numberBetween(2, 12),
        ];
    }
}
