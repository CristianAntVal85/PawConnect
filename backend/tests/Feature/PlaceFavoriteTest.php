<?php

use App\Models\PetFriendlyPlace;
use App\Models\User;
use Tests\TestCase;

class PlaceFavoriteTest extends TestCase
{
    private User $user;
    private User $otherUser;
    private PetFriendlyPlace $approvedPlace;
    private PetFriendlyPlace $approvedPlace2;
    private PetFriendlyPlace $pendingPlace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'user']);
        $this->otherUser = User::factory()->create(['role' => 'user']);

        // Lugares aprobados
        $this->approvedPlace = PetFriendlyPlace::factory()->create([
            'status' => 'approved',
            'created_by' => $this->otherUser->id,
        ]);

        $this->approvedPlace2 = PetFriendlyPlace::factory()->create([
            'status' => 'approved',
            'created_by' => $this->otherUser->id,
        ]);

        // Lugar pendiente
        $this->pendingPlace = PetFriendlyPlace::factory()->create([
            'status' => 'pending',
            'created_by' => $this->otherUser->id,
        ]);
    }

    /** @test */
    public function usuario_no_autenticado_no_puede_ver_favoritos()
    {
        $response = $this->getJson('/api/v1/places/favorites');

        $response->assertUnauthorized();
    }

    /** @test */
    public function usuario_autenticado_puede_ver_lista_de_favoritos_vacia()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/places/favorites');

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [],
            'pagination' => ['total', 'per_page', 'current_page', 'last_page'],
        ]);
        $this->assertCount(0, $response->json('data'));
    }

    /** @test */
    public function usuario_puede_agregar_lugar_aprobado_a_favoritos()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/places/{$this->approvedPlace->id}/favorite");

        $response->assertCreated();
        $response->assertJson([
            'message' => 'Lugar agregado a favoritos correctamente',
        ]);

        // Verificar que está en la lista de favoritos
        $this->assertTrue(
            $this->user->favoritePlaces()->where('place_id', $this->approvedPlace->id)->exists()
        );
    }

    /** @test */
    public function usuario_no_puede_agregar_lugar_pendiente_a_favoritos()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/places/{$this->pendingPlace->id}/favorite");

        $response->assertForbidden();
        $response->assertJson([
            'message' => 'No puedes agregar a favoritos un lugar no aprobado',
        ]);
    }

    /** @test */
    public function usuario_no_puede_agregar_mismo_lugar_dos_veces()
    {
        // Agregar primera vez
        $this->actingAs($this->user)
            ->postJson("/api/v1/places/{$this->approvedPlace->id}/favorite");

        // Intentar agregar segunda vez
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/places/{$this->approvedPlace->id}/favorite");

        $response->assertConflict();
        $response->assertJson([
            'message' => 'Este lugar ya está en tus favoritos',
        ]);
    }

    /** @test */
    public function usuario_puede_ver_multiples_favoritos()
    {
        // Agregar dos lugares a favoritos
        $this->actingAs($this->user)
            ->postJson("/api/v1/places/{$this->approvedPlace->id}/favorite");

        $this->actingAs($this->user)
            ->postJson("/api/v1/places/{$this->approvedPlace2->id}/favorite");

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/places/favorites');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertContains($this->approvedPlace->id, $ids);
        $this->assertContains($this->approvedPlace2->id, $ids);
    }

    /** @test */
    public function usuario_puede_remover_lugar_de_favoritos()
    {
        // Agregar a favoritos
        $this->actingAs($this->user)
            ->postJson("/api/v1/places/{$this->approvedPlace->id}/favorite");

        // Remover de favoritos
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/places/{$this->approvedPlace->id}/favorite");

        $response->assertOk();
        $response->assertJson([
            'message' => 'Lugar removido de favoritos correctamente',
        ]);

        // Verificar que no está en la lista
        $this->assertFalse(
            $this->user->favoritePlaces()->where('place_id', $this->approvedPlace->id)->exists()
        );
    }

    /** @test */
    public function usuario_no_puede_remover_lugar_que_no_esta_en_favoritos()
    {
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/places/{$this->approvedPlace->id}/favorite");

        $response->assertNotFound();
        $response->assertJson([
            'message' => 'Este lugar no está en tus favoritos',
        ]);
    }

    /** @test */
    public function favoritos_estan_aislados_por_usuario()
    {
        // Usuario 1 agrega lugar a favoritos
        $this->actingAs($this->user)
            ->postJson("/api/v1/places/{$this->approvedPlace->id}/favorite");

        // Usuario 2 no debería ver ese favorito
        $response = $this->actingAs($this->otherUser)
            ->getJson('/api/v1/places/favorites');

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertNotContains($this->approvedPlace->id, $ids);
    }

    /** @test */
    public function favoritos_contienen_informacion_completa_del_lugar()
    {
        // Agregar a favoritos
        $this->actingAs($this->user)
            ->postJson("/api/v1/places/{$this->approvedPlace->id}/favorite");

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/places/favorites');

        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                [
                    'id',
                    'name',
                    'category',
                    'description',
                    'address',
                    'lat',
                    'lng',
                    'phone',
                    'website',
                    'photo',
                    'status',
                    'created_by',
                    'created_at',
                    'updated_at',
                ]
            ],
            'pagination' => ['total', 'per_page', 'current_page', 'last_page'],
        ]);
    }

    /** @test */
    public function favoritos_estan_paginados()
    {
        // Crear múltiples lugares
        $places = PetFriendlyPlace::factory(25)->create([
            'status' => 'approved',
            'created_by' => $this->otherUser->id,
        ]);

        // Agregar todos a favoritos
        foreach ($places as $place) {
            $this->actingAs($this->user)
                ->postJson("/api/v1/places/{$place->id}/favorite");
        }

        // Primera página
        $response1 = $this->actingAs($this->user)
            ->getJson('/api/v1/places/favorites');

        $response1->assertOk();
        $this->assertCount(20, $response1->json('data')); // 20 por página
        $this->assertEquals(2, $response1->json('pagination.last_page'));

        // Segunda página
        $response2 = $this->actingAs($this->user)
            ->getJson('/api/v1/places/favorites?page=2');

        $response2->assertOk();
        $this->assertCount(5, $response2->json('data'));
    }

    /** @test */
    public function usuario_no_autenticado_no_puede_agregar_a_favoritos()
    {
        $response = $this->postJson("/api/v1/places/{$this->approvedPlace->id}/favorite");

        $response->assertUnauthorized();
    }

    /** @test */
    public function usuario_no_autenticado_no_puede_remover_de_favoritos()
    {
        $response = $this->deleteJson("/api/v1/places/{$this->approvedPlace->id}/favorite");

        $response->assertUnauthorized();
    }
}
