<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Database\Seeder;

class UserLocationSeeder extends Seeder
{
    /**
     * Centro de Madrid para coordenadas de referencia del seed.
     * Offset máximo ±0.12° ≈ 13 km, todos los usuarios quedan
     * dentro del radio de 25 km entre sí.
     */
    private const CENTER_LAT = 40.4168;
    private const CENTER_LNG = -3.7038;

    public function run(): void
    {
        User::query()->chunk(50, function ($users) {
            foreach ($users as $user) {
                // Solo crear si no tiene ya ninguna ubicación guardada
                if ($user->locations()->exists()) {
                    continue;
                }

                UserLocation::create([
                    'user_id'    => $user->id,
                    'name'       => 'Mi casa',
                    'lat'        => self::CENTER_LAT + fake()->randomFloat(4, -0.12, 0.12),
                    'lng'        => self::CENTER_LNG + fake()->randomFloat(4, -0.12, 0.12),
                    'is_default' => true,
                ]);
            }
        });
    }
}
