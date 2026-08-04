<?php

namespace Database\Factories;

use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $minWeight = fake()->optional(0.7)->randomFloat(2, 0.5, 25);
        $maxWeight = $minWeight !== null
            ? fake()->randomFloat(2, (float) $minWeight, 60)
            : fake()->optional(0.5)->randomFloat(2, 1, 60);

        return [
            'provider_id' => Provider::factory(),
            'name' => ucfirst(fake()->words(3, true)),
            'category' => fake()->randomElement(['food', 'hygiene', 'toys', 'accessories', 'health']),
            'description' => fake()->optional()->paragraph(),
            'price' => fake()->randomFloat(2, 5, 350),
            'stock' => fake()->numberBetween(0, 250),
            'min_weight_kg' => $minWeight,
            'max_weight_kg' => $maxWeight,
            'recommended_breeds' => fake()->optional()->randomElements(
                ['Labrador', 'Golden', 'Bulldog', 'Poodle', 'Criollo', 'Pastor Aleman'],
                fake()->numberBetween(1, 3)
            ),
            'photo' => fake()->optional()->randomElement([
                'products/sample-1.jpg',
                'products/sample-2.jpg',
                'products/sample-3.jpg',
            ]),
        ];
    }
}
