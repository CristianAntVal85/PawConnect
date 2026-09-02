<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserLocation>
 */
class UserLocationFactory extends Factory
{
    /**
     * Ciudades españolas con coordenadas reales.
     * Todos los usuarios del seed estarán en Madrid con variación de ±0.12 grados
     * (~13 km), garantizando que se encuentren dentro del radio de 25 km.
     */
    private const MADRID_CENTER = ['lat' => 40.4168, 'lng' => -3.7038];

    private const LOCATION_NAMES = [
        'Mi casa',
        'Trabajo',
        'Parque favorito',
        'Casa de mis padres',
        'Barrio',
    ];

    public function definition(): array
    {
        $center = self::MADRID_CENTER;

        return [
            'user_id'    => User::factory(),
            'name'       => fake()->randomElement(self::LOCATION_NAMES),
            'lat'        => $center['lat'] + fake()->randomFloat(4, -0.12, 0.12),
            'lng'        => $center['lng'] + fake()->randomFloat(4, -0.12, 0.12),
            'is_default' => true,
        ];
    }
}
