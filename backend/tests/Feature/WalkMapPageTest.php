<?php

use App\Models\Pet;
use App\Models\User;
use App\Models\Walk;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('un usuario autenticado puede ver el mapa de paseos', function () {
    $user = User::factory()->create();
    Walk::factory()->create([
        'title' => 'Parque del Retiro',
        'scheduled_at' => now()->addDay(),
    ]);

    $response = $this->actingAs($user)->get('/walks');

    $response->assertOk()
        ->assertSee('Mapa de paseos')
        ->assertSee('navigator.geolocation', false)
        ->assertSee('Parque del Retiro');
});

test('un usuario autenticado puede unirse a un paseo desde la vista web', function () {
    $user = User::factory()->create();
    $token = 'test-csrf-token';
    $pet = Pet::factory()->create([
        'user_id' => $user->id,
        'species' => 'dog',
        'available_for_walks' => true,
    ]);
    $walk = Walk::factory()->create([
        'scheduled_at' => now()->addDay(),
        'max_pets' => 3,
    ]);

    $response = $this->actingAs($user)
        ->withSession(['_token' => $token])
        ->post(route('web.walks.join', $walk), [
            '_token' => $token,
            'pet_id' => $pet->id,
        ]);

    $response->assertRedirect(route('web.walks.index'));
    $response->assertSessionHas('success', 'Tu mascota se unió al paseo correctamente.');

    $this->assertDatabaseHas('walk_pet', [
        'walk_id' => $walk->id,
        'pet_id' => $pet->id,
    ]);
});

test('un usuario autenticado puede abandonar un paseo desde la vista web', function () {
    $user = User::factory()->create();
    $token = 'test-csrf-token';
    $pet = Pet::factory()->create([
        'user_id' => $user->id,
        'species' => 'dog',
        'available_for_walks' => true,
    ]);
    $walk = Walk::factory()->create([
        'scheduled_at' => now()->addDay(),
    ]);
    $walk->pets()->attach($pet->id);

    $response = $this->actingAs($user)
        ->withSession(['_token' => $token])
        ->delete(route('web.walks.leave', $walk), [
            '_token' => $token,
            'pet_id' => $pet->id,
        ]);

    $response->assertRedirect(route('web.walks.index'));
    $response->assertSessionHas('success', 'Tu mascota salió del paseo correctamente.');

    $this->assertDatabaseMissing('walk_pet', [
        'walk_id' => $walk->id,
        'pet_id' => $pet->id,
    ]);
});
