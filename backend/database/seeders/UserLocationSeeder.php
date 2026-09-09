<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserLocationSeeder extends Seeder
{
    /**
     * Centro de Madrid para coordenadas de referencia del seed.
     * Offset máximo ±0.12° ≈ 13 km, todos los usuarios quedan
     * dentro del radio de 25 km entre sí.
     *
     * Nota: Las ubicaciones ahora se guardan directamente en users.lat/lng
     * (la tabla user_locations fue eliminada). Este seeder asegura que
     * todos los usuarios tengan ubicación.
     */
    private const CENTER_LAT = 40.4168;
    private const CENTER_LNG = -3.7038;

    public function run(): void
    {
        User::query()->chunk(50, function ($users) {
            foreach ($users as $user) {
                // Solo actualizar si no tiene ubicación
                if ($user->lat === null || $user->lng === null) {
                    $user->update([
                        'lat' => self::CENTER_LAT + fake()->randomFloat(4, -0.12, 0.12),
                        'lng' => self::CENTER_LNG + fake()->randomFloat(4, -0.12, 0.12),
                        'address' => fake()->address(),
                    ]);
                }
            }
        });
    }
}
