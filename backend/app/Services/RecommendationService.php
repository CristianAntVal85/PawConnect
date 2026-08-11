<?php

namespace App\Services;

use App\Models\Pet;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class RecommendationService
{
    /**
     * Recomienda productos para una mascota basándose en sus características
     * y su historial sanitario.
     */
    public function recommendForPet(Pet $pet, int $perPage = 20): LengthAwarePaginator
    {
        $query = Product::query()->with('provider');

        // 1. Filtrar por especie (match exacto o categoría general)
        $query->where(function (Builder $q) use ($pet) {
            $q->where('category', 'like', "%{$pet->species}%")
              ->orWhere('category', 'general')
              ->orWhere('category', 'multiespecie');
        });

        // 2. Filtrar por peso si el producto tiene rangos definidos
        if ($pet->weight) {
            $query->where(function (Builder $q) use ($pet) {
                // Incluir productos sin restricciones de peso
                $q->where(function (Builder $noRestriction) {
                    $noRestriction->whereNull('min_weight_kg')
                                  ->whereNull('max_weight_kg');
                })
                // O productos donde el peso está dentro del rango
                ->orWhere(function (Builder $inRange) use ($pet) {
                    $inRange->where('min_weight_kg', '<=', $pet->weight)
                            ->where(function (Builder $maxQ) use ($pet) {
                                $maxQ->whereNull('max_weight_kg')
                                     ->orWhere('max_weight_kg', '>=', $pet->weight);
                            });
                })
                // O productos donde solo hay mínimo y el peso lo cumple
                ->orWhere(function (Builder $onlyMin) use ($pet) {
                    $onlyMin->where('min_weight_kg', '<=', $pet->weight)
                            ->whereNull('max_weight_kg');
                })
                // O productos donde solo hay máximo y el peso lo cumple
                ->orWhere(function (Builder $onlyMax) use ($pet) {
                    $onlyMax->whereNull('min_weight_kg')
                            ->where('max_weight_kg', '>=', $pet->weight);
                });
            });
        }

        // 3. Priorizar productos que incluyen la raza en recommended_breeds
        if ($pet->breed) {
            $query->orderByRaw(
                "CASE WHEN JSON_CONTAINS(recommended_breeds, ?) THEN 0 ELSE 1 END",
                [json_encode($pet->breed)]
            );
        }

        // 4. Cruzar con historial sanitario
        $this->applyHealthRecordFilters($query, $pet);

        // 5. Ordenar por stock disponible y popularidad
        $query->where('stock', '>', 0)
              ->orderBy('stock', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Aplica filtros basados en el historial sanitario de la mascota.
     * 
     * Por ejemplo, si tiene registros de vacunación pendientes,
     * prioriza productos relacionados con salud/vacunas.
     */
    protected function applyHealthRecordFilters(Builder $query, Pet $pet): void
    {
        $healthRecords = $pet->healthRecords()
            ->where('notifications_active', true)
            ->whereNotNull('next_due_date')
            ->where('next_due_date', '<=', now()->addDays(30))
            ->get();

        if ($healthRecords->isEmpty()) {
            return;
        }

        $healthTypes = $healthRecords->pluck('type')->unique()->toArray();

        // Priorizar productos de categorías relacionadas con la salud
        if (in_array('vaccination', $healthTypes) || in_array('deworming', $healthTypes)) {
            $query->orderByRaw(
                "CASE WHEN category IN ('salud', 'veterinaria', 'higiene') THEN 0 ELSE 1 END"
            );
        }

        if (in_array('grooming', $healthTypes)) {
            $query->orderByRaw(
                "CASE WHEN category IN ('higiene', 'estetica', 'accesorios') THEN 0 ELSE 1 END"
            );
        }
    }

    /**
     * Obtiene productos relacionados con un tipo de servicio específico.
     * Utilizado cuando el usuario pregunta "¿Qué comprar?" en contexto de:
     * - Tipo de registro sanitario (vacunación, desparasitación, etc.)
     * - Visita a veterinario (cirugía, medicinas, etc.)
     * - Tipo de lugar (parque, playa, etc.)
     */
    public function getProductsByServiceType(Pet $pet, string $serviceType, int $perPage = 6): LengthAwarePaginator
    {
        $query = Product::query()->with('provider');

        // Filtrar por especie
        $query->where(function (Builder $q) use ($pet) {
            $q->where('category', 'like', "%{$pet->species}%")
              ->orWhere('category', 'general')
              ->orWhere('category', 'multiespecie');
        });

        // Filtrar por peso
        if ($pet->weight) {
            $query->where(function (Builder $q) use ($pet) {
                $q->where(function (Builder $noRestriction) {
                    $noRestriction->whereNull('min_weight_kg')
                                  ->whereNull('max_weight_kg');
                })
                ->orWhere(function (Builder $inRange) use ($pet) {
                    $inRange->where('min_weight_kg', '<=', $pet->weight)
                            ->where(function (Builder $maxQ) use ($pet) {
                                $maxQ->whereNull('max_weight_kg')
                                     ->orWhere('max_weight_kg', '>=', $pet->weight);
                            });
                })
                ->orWhere(function (Builder $onlyMin) use ($pet) {
                    $onlyMin->where('min_weight_kg', '<=', $pet->weight)
                            ->whereNull('max_weight_kg');
                })
                ->orWhere(function (Builder $onlyMax) use ($pet) {
                    $onlyMax->whereNull('min_weight_kg')
                            ->where('max_weight_kg', '>=', $pet->weight);
                });
            });
        }

        // Mapeo de tipos de servicio a categorías de productos
        $serviceTypeToCategoryMap = [
            'vaccination'   => ['salud', 'veterinaria', 'medicinas', 'jeringas'],
            'deworming'     => ['salud', 'veterinaria', 'medicinas', 'antiparasitarios'],
            'checkup'       => ['salud', 'veterinaria', 'accesorios', 'transportines'],
            'surgery'       => ['salud', 'veterinaria', 'vendajes', 'recuperación', 'conos'],
            'medication'    => ['salud', 'medicinas', 'veterinaria'],
            'dental'        => ['salud', 'higiene', 'dental'],
            'grooming'      => ['higiene', 'estetica', 'accesorios'],
            // Lugares
            'park'          => ['juguetes', 'correas', 'accesorios', 'deportes'],
            'beach'         => ['protección', 'toallas', 'accesorios', 'deportes'],
            'pipican'       => ['juguetes', 'bolsas', 'accesorios', 'higiene'],
            'shop'          => ['general', 'multiespecie'],
            'restaurant'    => ['accesorios', 'transportines'],
            'vet'           => ['salud', 'veterinaria', 'medicinas', 'transportines'],
            'hotel'         => ['camas', 'mantas', 'accesorios'],
        ];

        // Priorizar productos por categoría relacionada al servicio
        $relatedCategories = $serviceTypeToCategoryMap[$serviceType] ?? [];
        
        if (!empty($relatedCategories)) {
            $placeholders = implode(',', array_fill(0, count($relatedCategories), '?'));
            $query->orderByRaw(
                "CASE WHEN category IN ({$placeholders}) THEN 0 ELSE 1 END",
                $relatedCategories
            );
        }

        // Stock disponible
        $query->where('stock', '>', 0)
              ->orderBy('stock', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Obtiene una descripción legible del tipo de servicio.
     */
    public function getServiceTypeLabel(string $serviceType): string
    {
        $labels = [
            'vaccination'   => 'Vacunación',
            'deworming'     => 'Desparasitación',
            'checkup'       => 'Chequeo',
            'surgery'       => 'Cirugía',
            'medication'    => 'Medicamento',
            'dental'        => 'Limpieza dental',
            'grooming'      => 'Aseo',
            'park'          => 'Parque',
            'beach'         => 'Playa',
            'pipican'       => 'Pipicán',
            'shop'          => 'Tienda',
            'restaurant'    => 'Restaurante',
            'vet'           => 'Veterinario',
            'hotel'         => 'Hotel',
        ];

        return $labels[$serviceType] ?? ucfirst($serviceType);
    }

    /**
     * Obtiene un emoji descriptivo del tipo de servicio.
     */
    public function getServiceTypeEmoji(string $serviceType): string
    {
        $emojis = [
            'vaccination'   => '💉',
            'deworming'     => '🩺',
            'checkup'       => '👨‍⚕️',
            'surgery'       => '🏥',
            'medication'    => '💊',
            'dental'        => '🦷',
            'grooming'      => '✂️',
            'park'          => '🌳',
            'beach'         => '🏖️',
            'pipican'       => '🐕',
            'shop'          => '🛒',
            'restaurant'    => '🍽️',
            'vet'           => '🏥',
            'hotel'         => '🏨',
        ];

        return $emojis[$serviceType] ?? '🛍️';
    }

    /**
     * Obtiene productos por edad de la mascota.
     * 
     * Cachorro (< 1 año), Adulto (1-7 años), Senior (> 7 años)
     */
    public function recommendByAge(Pet $pet, int $perPage = 20): LengthAwarePaginator
    {
        $ageCategory = $this->getAgeCategory($pet);

        return Product::query()
            ->with('provider')
            ->where('category', 'like', "%{$ageCategory}%")
            ->orWhere('category', 'like', "%{$pet->species}%")
            ->where('stock', '>', 0)
            ->orderBy('stock', 'desc')
            ->paginate($perPage);
    }

    /**
     * Determina la categoría de edad de una mascota.
     */
    protected function getAgeCategory(Pet $pet): string
    {
        if (! $pet->age) {
            return 'adulto';
        }

        if ($pet->age < 1) {
            return 'cachorro';
        }

        if ($pet->age > 7) {
            return 'senior';
        }

        return 'adulto';
    }

    /**
     * Obtiene productos relacionados con una categoría específica
     * filtrados por las características de la mascota.
     */
    public function recommendByCategory(Pet $pet, string $category, int $perPage = 20): LengthAwarePaginator
    {
        $query = Product::query()
            ->with('provider')
            ->where('category', $category);

        // Aplicar filtros de peso si el producto los tiene
        if ($pet->weight) {
            $query->where(function (Builder $q) use ($pet) {
                $q->whereNull('min_weight_kg')
                  ->orWhere(function (Builder $subQ) use ($pet) {
                      $subQ->where('min_weight_kg', '<=', $pet->weight)
                           ->where(function (Builder $maxQ) use ($pet) {
                               $maxQ->whereNull('max_weight_kg')
                                    ->orWhere('max_weight_kg', '>=', $pet->weight);
                           });
                  });
            });
        }

        // Priorizar productos que incluyen la raza
        if ($pet->breed) {
            $query->orderByRaw(
                "CASE WHEN JSON_CONTAINS(recommended_breeds, ?) THEN 0 ELSE 1 END",
                [json_encode($pet->breed)]
            );
        }

        return $query->where('stock', '>', 0)
                     ->orderBy('stock', 'desc')
                     ->paginate($perPage);
    }
}
