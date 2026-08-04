<?php

namespace Database\Seeders;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Database\Seeder;

class PetSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $userIds = User::query()->pluck('id');

        if ($userIds->isEmpty()) {
            $userIds = User::factory()->count(10)->create()->pluck('id');
        }

        Pet::factory()
            ->count(20)
            ->state(fn () => ['user_id' => $userIds->random()])
            ->create();
    }
}
