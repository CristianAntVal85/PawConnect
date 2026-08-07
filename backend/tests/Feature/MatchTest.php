<?php

use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('el endpoint devuelve candidatos compatibles y excluye mascotas propias y lejanas', function () {
    $user = actingAsUser();
    $user->forceFill(['lat' => 0, 'lng' => 0])->save();

    Pet::factory()->create([
        'user_id' => $user->id,
        'species' => 'dog',
        'name' => 'Rex',
        'size' => 'medium',
        'energy_level' => 'high',
        'available_for_walks' => true,
    ]);

    $compatiblePet = Pet::factory()->create([
        'user_id' => User::factory()->create(['lat' => 0.001, 'lng' => 0.001])->id,
        'species' => 'dog',
        'name' => 'Milo',
        'size' => 'medium',
        'energy_level' => 'high',
        'available_for_walks' => true,
    ]);

    Pet::factory()->create([
        'user_id' => User::factory()->create(['lat' => 45, 'lng' => 45])->id,
        'species' => 'dog',
        'name' => 'Luna',
        'size' => 'large',
        'energy_level' => 'low',
        'available_for_walks' => false,
    ]);

    $response = $this->getJson('/api/v1/matches/candidates');

    $response->assertOk()
        ->assertJsonCount(1, 'data');

    expect($response->json('data.0.id'))->toBe($compatiblePet->id);
});
