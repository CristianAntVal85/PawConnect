<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => 'admin@pawconnect.test',
        ], [
            'name' => 'Admin User',
            'password' => 'Password123',
            'role' => User::ROLE_ADMIN,
            'remember_token' => Str::random(10),
        ]);

        $usersToCreate = max(0, 10 - User::query()->count());

        if ($usersToCreate > 0) {
            User::factory()->count($usersToCreate)->create();
        }
    }
}
