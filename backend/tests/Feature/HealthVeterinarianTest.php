<?php

namespace Tests\Feature;

use App\Models\HealthRecord;
use App\Models\Pet;
use App\Models\PetFriendlyPlace;
use App\Models\User;
use App\Services\HealthStatusService;
use App\Services\HealthVeterinarianService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthVeterinarianTest extends TestCase
{
    use RefreshDatabase;

    private HealthVeterinarianService $service;
    private User $user;
    private Pet $pet;
    private HealthStatusService $statusService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(HealthVeterinarianService::class);
        $this->statusService = app(HealthStatusService::class);

        // Crear usuario con localización
        $this->user = User::factory()->create([
            'lat' => -33.8688,  // Buenos Aires
            'lng' => -56.1645,
        ]);

        // Crear mascota del usuario
        $this->pet = Pet::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * @test
     * getCriticalHealthRecords retorna solo registros amarillos/rojos
     */
    public function getCriticalHealthRecords_returns_only_yellow_and_red_records(): void
    {
        // Crear registros con diferentes estados
        // Verde: más de 7 días en el futuro
        HealthRecord::factory()->create([
            'pet_id' => $this->pet->id,
            'type' => 'vaccination',
            'next_due_date' => now()->addDays(15)->toDateString(),
        ]);

        // Amarillo: 0-7 días en el futuro
        HealthRecord::factory()->create([
            'pet_id' => $this->pet->id,
            'type' => 'deworming',
            'next_due_date' => now()->addDays(3)->toDateString(),
        ]);

        // Rojo: vencido
        HealthRecord::factory()->create([
            'pet_id' => $this->pet->id,
            'type' => 'checkup',
            'next_due_date' => now()->subDays(5)->toDateString(),
        ]);

        $critical = $this->service->getCriticalHealthRecords($this->pet);

        $this->assertCount(2, $critical);
        $this->assertTrue($critical->every(function ($r) {
            $status = $this->statusService->getStatus($r);
            return in_array($status['status'], ['yellow', 'red']);
        }));
    }

    /**
     * @test
     * getRecommendedVeterinarians retorna empty cuando no hay registros críticos
     */
    public function getRecommendedVeterinarians_returns_empty_when_no_critical_records(): void
    {
        // Crear solo registros verdes
        HealthRecord::factory(3)->create([
            'pet_id' => $this->pet->id,
            'next_due_date' => now()->addDays(30)->toDateString(),
        ]);

        $vets = $this->service->getRecommendedVeterinarians($this->pet, $this->user);

        $this->assertTrue($vets->isEmpty());
    }

    /**
     * @test
     * getRecommendedVeterinarians encuentra vets cercanos
     */
    public function getRecommendedVeterinarians_finds_nearby_vets(): void
    {
        // Crear registro crítico (amarillo)
        HealthRecord::factory()->create([
            'pet_id' => $this->pet->id,
            'type' => 'vaccination',
            'next_due_date' => now()->addDays(2)->toDateString(),
        ]);

        // Crear veterinario aprobado cerca del usuario
        $vet = PetFriendlyPlace::factory()->create([
            'category' => 'vet',
            'status' => 'approved',
            'lat' => -33.8688,   // Misma ubicación del usuario
            'lng' => -56.1645,
        ]);

        $vets = $this->service->getRecommendedVeterinarians($this->pet, $this->user, 10);

        $this->assertNotEmpty($vets);
        $this->assertTrue($vets->pluck('place_id')->contains($vet->id));
    }

    /**
     * @test
     * getRecommendedVeterinarians respeta el radio de búsqueda
     */
    public function getRecommendedVeterinarians_respects_search_radius(): void
    {
        // Crear registro crítico
        HealthRecord::factory()->create([
            'pet_id' => $this->pet->id,
            'next_due_date' => now()->addDays(1)->toDateString(),
        ]);

        // Veterinario lejano (>15km)
        PetFriendlyPlace::factory()->create([
            'category' => 'vet',
            'status' => 'approved',
            'lat' => -33.7000,   // ~10km de distancia
            'lng' => -56.2500,
        ]);

        // Veterinario cercano (<5km)
        $nearVet = PetFriendlyPlace::factory()->create([
            'category' => 'vet',
            'status' => 'approved',
            'lat' => -33.8600,   // ~1km de distancia
            'lng' => -56.1600,
        ]);

        $vets = $this->service->getRecommendedVeterinarians($this->pet, $this->user, 5);

        $this->assertTrue($vets->pluck('place_id')->contains($nearVet->id));
        $this->assertLessThanOrEqual(5, $vets->count());
    }

    /**
     * @test
     * getUrgencyLevel retorna 'high' si hay registros rojos
     */
    public function getUrgencyLevel_returns_high_for_red_records(): void
    {
        // Crear registro rojo (vencido)
        HealthRecord::factory()->create([
            'pet_id' => $this->pet->id,
            'next_due_date' => now()->subDays(10)->toDateString(),
        ]);

        $urgency = $this->service->getUrgencyLevel($this->pet);

        $this->assertEquals('high', $urgency);
    }

    /**
     * @test
     * getUrgencyLevel retorna 'medium' si solo hay registros amarillos
     */
    public function getUrgencyLevel_returns_medium_for_yellow_only(): void
    {
        // Crear solo registro amarillo
        HealthRecord::factory()->create([
            'pet_id' => $this->pet->id,
            'next_due_date' => now()->addDays(3)->toDateString(),
        ]);

        $urgency = $this->service->getUrgencyLevel($this->pet);

        $this->assertEquals('medium', $urgency);
    }

    /**
     * @test
     * API endpoint retorna veterinarios recomendados
     */
    public function api_endpoint_returns_recommended_veterinarians(): void
    {
        // Crear registro crítico
        HealthRecord::factory()->create([
            'pet_id' => $this->pet->id,
            'type' => 'vaccination',
            'next_due_date' => now()->addDays(2)->toDateString(),
        ]);

        // Crear veterinario cercano
        PetFriendlyPlace::factory()->create([
            'category' => 'vet',
            'status' => 'approved',
            'lat' => -33.8688,
            'lng' => -56.1645,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/pets/{$this->pet->id}/health/veterinarians");

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'place' => [
                        'id', 'name', 'category', 'address', 'phone', 'website'
                    ],
                    'distance',
                    'distance_unit',
                    'needed_services',
                    'urgency'
                ]
            ]
        ]);
    }

    /**
     * @test
     * API endpoint requiere autenticación
     */
    public function api_endpoint_requires_authentication(): void
    {
        $response = $this->getJson("/api/v1/pets/{$this->pet->id}/health/veterinarians");

        $response->assertUnauthorized();
    }

    /**
     * @test
     * API endpoint retorna 403 si no es el dueño
     */
    public function api_endpoint_returns_403_if_not_owner(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->getJson("/api/v1/pets/{$this->pet->id}/health/veterinarians");

        $response->assertForbidden();
    }

    /**
     * @test
     * getServicesDescription formatea tipos correctamente
     */
    public function getServicesDescription_formats_types_correctly(): void
    {
        $services = ['vaccination', 'deworming', 'checkup'];

        $description = $this->service->getServicesDescription($services);

        $this->assertStringContainsString('Vacunación', $description);
        $this->assertStringContainsString('Desparasitación', $description);
        $this->assertStringContainsString('Revisión general', $description);
    }

    /**
     * @test
     * getUrgencyEmoji retorna emojis correctos
     */
    public function getUrgencyEmoji_returns_correct_emojis(): void
    {
        $this->assertEquals('🚨', $this->service->getUrgencyEmoji('high'));
        $this->assertEquals('⏰', $this->service->getUrgencyEmoji('medium'));
        $this->assertEquals('ℹ️', $this->service->getUrgencyEmoji('default'));
    }
}
