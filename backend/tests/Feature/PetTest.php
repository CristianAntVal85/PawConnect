<?php

use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

// ── Autorización ──────────────────────────────────────────────────────────

test('un invitado no puede acceder a las rutas de mascotas', function () {
    $this->getJson('/api/v1/pets')->assertUnauthorized();
    $this->postJson('/api/v1/pets', [])->assertUnauthorized();
});

// ── Crear mascota ─────────────────────────────────────────────────────────

test('un usuario autenticado puede crear una mascota', function () {
    $user = actingAsUser();

    $response = $this->postJson('/api/v1/pets', [
        'species' => 'dog',
        'name' => 'Firulais',
        'breed' => 'Labrador',
        'age' => 3,
        'weight' => 25.5,
        'size' => 'medium',
        'energy_level' => 'high',
        'available_for_walks' => true,
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => ['id', 'name', 'species', 'breed', 'user_id'],
        ]);

    expect($response->json('data.name'))->toBe('Firulais');
    expect($response->json('data.user_id'))->toBe($user->id);

    $this->assertDatabaseHas('pets', [
        'user_id' => $user->id,
        'name' => 'Firulais',
        'species' => 'dog',
    ]);
});

test('la creación falla sin campos requeridos', function () {
    actingAsUser();

    $this->postJson('/api/v1/pets', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['species', 'name']);
});

test('la creación valida el peso máximo', function () {
    actingAsUser();

    $this->postJson('/api/v1/pets', [
        'species' => 'dog',
        'name' => 'Test',
        'weight' => 9999,
    ])->assertUnprocessable()
      ->assertJsonValidationErrors(['weight']);
});

// ── Listar mascotas ───────────────────────────────────────────────────────

test('un usuario puede listar solo sus propias mascotas', function () {
    $user = actingAsUser();
    $otherUser = User::factory()->create();

    Pet::factory()->count(3)->create(['user_id' => $user->id]);
    Pet::factory()->count(2)->create(['user_id' => $otherUser->id]);

    $response = $this->getJson('/api/v1/pets');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

// ── Actualizar mascota ────────────────────────────────────────────────────

test('un usuario puede actualizar su propia mascota', function () {
    $user = actingAsUser();
    $pet = Pet::factory()->create([
        'user_id' => $user->id,
        'name' => 'Nombre Original',
    ]);

    $this->putJson('/api/v1/pets/'.$pet->id, [
        'name' => 'Nombre Actualizado',
        'age' => 5,
    ])->assertOk();

    $this->assertDatabaseHas('pets', [
        'id' => $pet->id,
        'name' => 'Nombre Actualizado',
        'age' => 5,
    ]);
});

test('un usuario NO puede actualizar mascotas de otros usuarios', function () {
    $owner = User::factory()->create();
    $intruder = actingAsUser();

    $pet = Pet::factory()->create([
        'user_id' => $owner->id,
        'name' => 'Rocky',
    ]);

    $this->putJson('/api/v1/pets/'.$pet->id, [
        'name' => 'Hackeo',
    ])->assertForbidden();

    $this->assertDatabaseHas('pets', [
        'id' => $pet->id,
        'name' => 'Rocky',
    ]);
});

// ── Eliminar mascota ──────────────────────────────────────────────────────

test('un usuario puede eliminar su propia mascota', function () {
    $user = actingAsUser();
    $pet = Pet::factory()->create(['user_id' => $user->id]);

    $this->deleteJson('/api/v1/pets/'.$pet->id)->assertOk();

    $this->assertDatabaseMissing('pets', ['id' => $pet->id]);
});

test('un usuario NO puede eliminar mascotas de otros usuarios', function () {
    $owner = User::factory()->create();
    $intruder = actingAsUser();

    $pet = Pet::factory()->create(['user_id' => $owner->id]);

    $this->deleteJson('/api/v1/pets/'.$pet->id)->assertForbidden();

    $this->assertDatabaseHas('pets', ['id' => $pet->id]);
});
