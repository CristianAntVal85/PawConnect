<?php

use App\Models\PetFriendlyPlace;
use App\Models\User;
use Tests\TestCase;

class PlaceTest extends TestCase
{
    private User $user;
    private User $admin;
    private PetFriendlyPlace $approvedPlace;
    private PetFriendlyPlace $pendingPlace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        // Lugar aprobado
        $this->approvedPlace = PetFriendlyPlace::factory()->create([
            'status' => 'approved',
            'created_by' => $this->user->id,
        ]);

        // Lugar pendiente
        $this->pendingPlace = PetFriendlyPlace::factory()->create([
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);
    }

    /** @test */
    public function usuarios_ven_solo_lugares_aprobados()
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('api.v1.places.index'));

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');

        $this->assertContains($this->approvedPlace->id, $ids);
        $this->assertNotContains($this->pendingPlace->id, $ids);
    }

    /** @test */
    public function admin_ve_todos_lugares_incluyendo_pendientes()
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('api.v1.places.index'));

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');

        $this->assertContains($this->approvedPlace->id, $ids);
        $this->assertContains($this->pendingPlace->id, $ids);
    }

    /** @test */
    public function admin_puede_filtrar_lugares_por_status()
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('api.v1.places.index', ['status' => 'pending']));

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');

        $this->assertContains($this->pendingPlace->id, $ids);
        $this->assertNotContains($this->approvedPlace->id, $ids);
    }

    /** @test */
    public function usuario_puede_sugerir_nuevo_lugar()
    {
        $data = [
            'name'        => 'Parque Nuevo',
            'category'    => 'park',
            'description' => 'Un hermoso parque para pasear mascotas',
            'lat'         => -33.9249,
            'lng'         => -18.4241,
            'address'     => 'Calle Principal 123',
            'phone'       => '+34 911 234 567',
            'website'     => 'https://example.com',
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('api.v1.places.store'), $data);

        $response->assertCreated();
        $response->assertJsonPath('data.status', 'pending');
        $response->assertJsonPath('data.created_by', $this->user->id);

        $this->assertDatabaseHas('petfriendly_places', [
            'name'   => 'Parque Nuevo',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function validacion_de_categoria_en_sugerencia()
    {
        $data = [
            'name'        => 'Lugar Inválido',
            'category'    => 'invalid_category',
            'description' => 'Descripción',
            'lat'         => -33.9249,
            'lng'         => -18.4241,
            'address'     => 'Dirección',
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('api.v1.places.store'), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('category');
    }

    /** @test */
    public function validacion_de_coordenadas()
    {
        $data = [
            'name'        => 'Lugar Inválido',
            'category'    => 'park',
            'description' => 'Descripción',
            'lat'         => 95, // Fuera de rango
            'lng'         => -18.4241,
            'address'     => 'Dirección',
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('api.v1.places.store'), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('lat');
    }

    /** @test */
    public function usuario_puede_ver_lugar_aprobado()
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('api.v1.places.show', $this->approvedPlace->id));

        $response->assertOk();
        $response->assertJsonPath('data.id', $this->approvedPlace->id);
    }

    /** @test */
    public function usuario_no_puede_ver_lugar_pendiente_de_otro()
    {
        $otherUser = User::factory()->create();
        $response = $this->actingAs($otherUser)
            ->getJson(route('api.v1.places.show', $this->pendingPlace->id));

        $response->assertForbidden();
    }

    /** @test */
    public function creador_puede_actualizar_su_lugar()
    {
        $data = [
            'name' => 'Nombre Actualizado',
        ];

        $response = $this->actingAs($this->user)
            ->putJson(route('api.v1.places.update', $this->pendingPlace->id), $data);

        $response->assertOk();
        $response->assertJsonPath('data.name', 'Nombre Actualizado');

        $this->assertDatabaseHas('petfriendly_places', [
            'id'   => $this->pendingPlace->id,
            'name' => 'Nombre Actualizado',
        ]);
    }

    /** @test */
    public function usuario_no_puede_actualizar_lugar_ajeno()
    {
        $otherUser = User::factory()->create();
        $data = ['name' => 'Nombre Hackead o'];

        $response = $this->actingAs($otherUser)
            ->putJson(route('api.v1.places.update', $this->pendingPlace->id), $data);

        $response->assertForbidden();
    }

    /** @test */
    public function admin_puede_eliminar_cualquier_lugar()
    {
        $response = $this->actingAs($this->admin)
            ->deleteJson(route('api.v1.places.destroy', $this->pendingPlace->id));

        $response->assertOk();

        $this->assertDatabaseMissing('petfriendly_places', [
            'id' => $this->pendingPlace->id,
        ]);
    }

    /** @test */
    public function admin_puede_aprobar_lugar()
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('api.v1.admin.places.approve', $this->pendingPlace->id), [
                'approved' => true,
            ]);

        $response->assertOk();
        $response->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('petfriendly_places', [
            'id'     => $this->pendingPlace->id,
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function admin_puede_rechazar_lugar()
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('api.v1.admin.places.approve', $this->pendingPlace->id), [
                'approved' => false,
                'reason'   => 'Información incompleta',
            ]);

        $response->assertOk();
        $response->assertJsonPath('data.status', 'rejected');
    }

    /** @test */
    public function usuario_normal_no_puede_aprobar_lugares()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('api.v1.admin.places.approve', $this->pendingPlace->id), [
                'approved' => true,
            ]);

        $response->assertForbidden();
    }

    /** @test */
    public function filtro_por_categoria_funciona()
    {
        PetFriendlyPlace::factory()->create(['status' => 'approved', 'category' => 'vet']);
        PetFriendlyPlace::factory()->create(['status' => 'approved', 'category' => 'vet']);
        PetFriendlyPlace::factory()->create(['status' => 'approved', 'category' => 'park']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.v1.places.index', ['category' => 'vet']));

        $response->assertOk();
        $categories = collect($response->json('data'))->pluck('category')->unique();

        $this->assertTrue($categories->contains('vet'));
        $this->assertFalse($categories->contains('park'));
    }

    /** @test */
    public function busqueda_por_nombre_funciona()
    {
        PetFriendlyPlace::factory()->create([
            'status' => 'approved',
            'name' => 'Veterinaria Central',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.v1.places.index', ['search' => 'Veterinaria']));

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');

        $this->assertTrue($names->contains('Veterinaria Central'));
    }

    /** @test */
    public function paginacion_de_lugares_funciona()
    {
        // Crear 30 lugares adicionales (más allá del approvedPlace del setUp)
        PetFriendlyPlace::factory()->count(30)->create(['status' => 'approved']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.v1.places.index'));

        $response->assertOk();
        // Verifica que haya al menos 31 lugares totales (30 nuevos + 1 del setUp)
        $this->assertGreaterThanOrEqual(31, $response->json('pagination.total'));
        expect($response->json('pagination.current_page'))->toEqual(1);
    }
}
