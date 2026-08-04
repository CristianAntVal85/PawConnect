<?php

namespace Database\Factories;

use App\Models\Pet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PetPhoto>
 */
class PetPhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pet_id' => Pet::factory(),
            'path' => 'pets/demo/photo-'.$this->faker->unique()->numberBetween(1, 9999).'.jpg',
            'display_order' => 0,
        ];
    }
}