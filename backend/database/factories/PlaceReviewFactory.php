<?php

namespace Database\Factories;

use App\Models\PetFriendlyPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlaceReview>
 */
class PlaceReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'   => User::factory(),
            'place_id'  => PetFriendlyPlace::factory(),
            'rating'    => $this->faker->numberBetween(1, 5),
            'body'      => $this->faker->text(200),
        ];
    }
}
