<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pet>
 */
class PetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $species = fake()->randomElement(['dog', 'cat', 'rabbit', 'bird']);

        return [
            'user_id' => User::factory(),
            'species' => $species,
            'name' => fake()->firstName(),
            'breed' => fake()->randomElement(['Labrador', 'Golden', 'Criollo', 'Pug', 'Siames', 'Persa', 'Mestizo']),
            'age' => fake()->numberBetween(0, 18),
            'weight' => fake()->randomFloat(2, 0.5, 60),
            'size' => fake()->randomElement(['small', 'medium', 'large']),
            'energy_level' => fake()->randomElement(['low', 'medium', 'high']),
            'personality' => fake()->sentence(),
            'available_for_walks' => $species === 'dog' ? fake()->boolean(85) : fake()->boolean(35),
        ];
    }
}
