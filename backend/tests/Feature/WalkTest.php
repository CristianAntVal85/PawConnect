<?php

use App\Models\Pet;
use App\Models\User;
use App\Models\Walk;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('un usuario autenticado puede crear un paseo', function () {
    $user = actingAsUser();

    $response = $this->postJson('/api/v1/walks', [
        'title' => 'Paseo por el parque',
        'description' => 'Salida grupal del domingo',
        'lat' => 40.4168,
        'lng' => -3.7038,
        'scheduled_at' => now()->addDay()->toISOString(),
        'max_pets' => 6,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'Paseo por el parque')
        ->assertJsonPath('data.creator_id', $user->id)
        ->assertJsonPath('data.participant_count', 0);

    $this->assertDatabaseHas('walks', [
        'creator_id' => $user->id,
        'title' => 'Paseo por el parque',
    ]);
});

test('el listado de paseos filtra por cercanía', function () {
    actingAsUser();

    $nearWalk = Walk::factory()->create([
        'lat' => 40.4168,
        'lng' => -3.7038,
        'scheduled_at' => now()->addDays(2),
    ]);

    Walk::factory()->create([
        'lat' => 41.3874,
        'lng' => 2.1686,
        'scheduled_at' => now()->addDays(2),
    ]);

    $response = $this->getJson('/api/v1/walks?lat=40.4168&lng=-3.7038&radius_km=5');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $nearWalk->id);
});

test('un usuario puede unirse a un paseo con una mascota canina propia', function () {
    $user = actingAsUser();
    $pet = Pet::factory()->create([
        'user_id' => $user->id,
        'species' => 'dog',
        'available_for_walks' => true,
        'name' => 'Nala',
    ]);
    $walk = Walk::factory()->create([
        'scheduled_at' => now()->addDay(),
        'max_pets' => 4,
    ]);

    $response = $this->postJson('/api/v1/walks/'.$walk->id.'/join', [
        'pet_id' => $pet->id,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.participant_count', 1)
        ->assertJsonPath('data.participants.0.id', $pet->id);

    $this->assertDatabaseHas('walk_pet', [
        'walk_id' => $walk->id,
        'pet_id' => $pet->id,
    ]);
});

test('un paseo no acepta más mascotas que su capacidad máxima', function () {
    $user = actingAsUser();
    $joiningPet = Pet::factory()->create([
        'user_id' => $user->id,
        'species' => 'dog',
        'available_for_walks' => true,
    ]);
    $existingPet = Pet::factory()->create([
        'species' => 'dog',
        'available_for_walks' => true,
    ]);
    $walk = Walk::factory()->create([
        'max_pets' => 1,
        'scheduled_at' => now()->addDay(),
    ]);
    $walk->pets()->attach($existingPet->id);

    $this->postJson('/api/v1/walks/'.$walk->id.'/join', [
        'pet_id' => $joiningPet->id,
    ])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'El paseo ya alcanzó el máximo de mascotas permitidas.');
});

test('un usuario puede abandonar un paseo sin indicar pet_id si solo tiene una mascota participante', function () {
    $user = actingAsUser();
    $pet = Pet::factory()->create([
        'user_id' => $user->id,
        'species' => 'dog',
        'available_for_walks' => true,
    ]);
    $walk = Walk::factory()->create([
        'scheduled_at' => now()->addDay(),
    ]);
    $walk->pets()->attach($pet->id);

    $response = $this->deleteJson('/api/v1/walks/'.$walk->id.'/leave');

    $response->assertOk()
        ->assertJsonPath('data.participant_count', 0);

    $this->assertDatabaseMissing('walk_pet', [
        'walk_id' => $walk->id,
        'pet_id' => $pet->id,
    ]);
});

test('solo el creador o un admin puede actualizar o eliminar un paseo', function () {
    $owner = User::factory()->create();
    $walk = Walk::factory()->create([
        'creator_id' => $owner->id,
        'title' => 'Original',
        'scheduled_at' => now()->addDay(),
    ]);

    actingAsUser();

    $this->putJson('/api/v1/walks/'.$walk->id, [
        'title' => 'Intento ajeno',
    ])->assertForbidden();

    $this->deleteJson('/api/v1/walks/'.$walk->id)->assertForbidden();
});
