<?php

namespace Database\Factories;

use App\Models\Pet;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $price = fake()->randomFloat(2, 5, 200);

        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'pet_id' => Pet::factory(),
            'quantity' => $quantity,
            'total_price' => round($price * $quantity, 2),
            'shipping_address' => fake()->address(),
            'payment_method' => fake()->randomElement(['card', 'cash']),
            'status' => 'pending',
            'stripe_payment_id' => fake()->optional()->uuid(),
        ];
    }
}
