<?php

namespace Database\Seeders;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Database\Seeder;

class PetSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Crea mascotas (preferiblemente perros) asociadas a usuarios existentes.
     * Solo los perros con available_for_walks=true aparecen en swipe.
     */
    public function run(): void
    {
        $userIds = User::query()->pluck('id');

        if ($userIds->isEmpty()) {
            $userIds = User::factory()->count(10)->create()->pluck('id');
        }

        // Crear mascotas con preferencia por perros (species = 'dog')
        Pet::factory()
            ->count(30)
            ->state(function () use ($userIds) {
                return [
                    'user_id' => $userIds->random(),
                    'species' => fake()->randomElement(array_fill(0, 7, 'dog') + ['cat', 'rabbit', 'bird']), // 70% perros
                ];
            })
            ->create();
    }
}
