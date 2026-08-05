<?php

use App\Models\HealthRecord;
use App\Models\Pet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Crear registro sanitario ──────────────────────────────────────────────────────

test('un usuario puede crear un registro sanitario para su mascota', function () {
    $user = actingAsUser();
    $pet = Pet::factory()->create(['user_id' => $user->id]);

    $response = $this->postJson("/api/v1/pets/{$pet->id}/health", [
        'type' => 'vaccination',
        'last_date' => '2026-01-15',
        'periodicity_days' => 365,
        'notes' => 'Vacuna antirrábica anual',
        'notifications_active' => true,
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => ['id', 'type', 'last_date', 'next_due_date'],
        ]);

    expect($response->json('data.type'))->toBe('vaccination');
    expect($response->json('data.next_due_date'))->toBe('2027-01-15');

    $this->assertDatabaseHas('health_records', [
        'pet_id' => $pet->id,
        'type' => 'vaccination',
    ]);
});

test('el campo next_due_date se calcula automáticamente', function () {
    $user = actingAsUser();
    $pet = Pet::factory()->create(['user_id' => $user->id]);

    $response = $this->postJson("/api/v1/pets/{$pet->id}/health", [
        'type' => 'deworming',
        'last_date' => '2026-05-01',
        'periodicity_days' => 90,
    ]);

    // next_due_date = 2026-05-01 + 90 días = 2026-07-30
    expect($response->json('data.next_due_date'))->toBe('2026-07-30');
});

test('un usuario NO puede crear registros sanitarios en mascotas ajenas', function () {
    $owner = \App\Models\User::factory()->create();
    $intruder = actingAsUser();

    $pet = Pet::factory()->create(['user_id' => $owner->id]);

    $this->postJson("/api/v1/pets/{$pet->id}/health", [
        'type' => 'vaccination',
        'last_date' => '2026-01-01',
    ])->assertForbidden();
});

// ── Listar registros sanitarios ──────────────────────────────────────────────────

test('un usuario puede listar los registros sanitarios de su mascota', function () {
    $user = actingAsUser();
    $pet = Pet::factory()->create(['user_id' => $user->id]);

    HealthRecord::factory()->count(3)->create(['pet_id' => $pet->id]);

    $response = $this->getJson("/api/v1/pets/{$pet->id}/health");

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('un usuario NO puede ver registros sanitarios de mascotas ajenas', function () {
    $owner = \App\Models\User::factory()->create();
    $intruder = actingAsUser();

    $pet = Pet::factory()->create(['user_id' => $owner->id]);
    HealthRecord::factory()->create(['pet_id' => $pet->id]);

    $this->getJson("/api/v1/pets/{$pet->id}/health")->assertForbidden();
});

// ── Actualizar registro sanitario ──────────────────────────────────────────────────

test('un usuario puede actualizar un registro sanitario de su mascota', function () {
    $user = actingAsUser();
    $pet = Pet::factory()->create(['user_id' => $user->id]);
    $record = HealthRecord::factory()->create([
        'pet_id' => $pet->id,
        'type' => 'vaccination',
        'notes' => 'Nota original',
    ]);

    $this->putJson("/api/v1/pets/{$pet->id}/health/{$record->id}", [
        'notes' => 'Nota actualizada',
        'notifications_active' => false,
    ])->assertOk();

    $this->assertDatabaseHas('health_records', [
        'id' => $record->id,
        'notes' => 'Nota actualizada',
        'notifications_active' => false,
    ]);
});

// ── Eliminar registro sanitario ──────────────────────────────────────────────────

test('un usuario puede eliminar un registro sanitario de su mascota', function () {
    $user = actingAsUser();
    $pet = Pet::factory()->create(['user_id' => $user->id]);
    $record = HealthRecord::factory()->create(['pet_id' => $pet->id]);

    $this->deleteJson("/api/v1/pets/{$pet->id}/health/{$record->id}")->assertOk();

    $this->assertDatabaseMissing('health_records', ['id' => $record->id]);
});

// ── Validaciones ──────────────────────────────────────────────────────────────────

test('la creación falla sin campos requeridos', function () {
    $user = actingAsUser();
    $pet = Pet::factory()->create(['user_id' => $user->id]);

    $this->postJson("/api/v1/pets/{$pet->id}/health", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['type']);
});

test('el tipo de registro debe ser válido', function () {
    $user = actingAsUser();
    $pet = Pet::factory()->create(['user_id' => $user->id]);

    $this->postJson("/api/v1/pets/{$pet->id}/health", [
        'type' => 'tipo_invalido',
        'last_date' => '2026-01-01',
    ])->assertUnprocessable()
      ->assertJsonValidationErrors(['type']);
});
