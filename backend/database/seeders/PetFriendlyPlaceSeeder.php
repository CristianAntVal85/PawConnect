<?php

namespace Database\Seeders;

use App\Models\PetFriendlyPlace;
use App\Models\User;
use Illuminate\Database\Seeder;

class PetFriendlyPlaceSeeder extends Seeder
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

        PetFriendlyPlace::factory()
            ->count(15)
            ->state(fn () => ['created_by' => $userIds->random()])
            ->create();
    }
}
