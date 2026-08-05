<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HealthRecord>
 */
class HealthRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['vaccination', 'deworming', 'checkup', 'surgery', 'medication'];
        $lastDate = fake()->dateTimeBetween('-1 year', 'now');
        $periodicityDays = fake()->randomElement([30, 90, 180, 365]);

        return [
            'pet_id' => \App\Models\Pet::factory(),
            'type' => fake()->randomElement($types),
            'last_date' => $lastDate->format('Y-m-d'),
            'periodicity_days' => $periodicityDays,
            'notes' => fake()->optional()->sentence(),
            'notifications_active' => fake()->boolean(70),
            'next_due_date' => (clone $lastDate)->modify("+{$periodicityDays} days")->format('Y-m-d'),
        ];
    }
}
