<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Registro ──────────────────────────────────────────────────────────────

test('un usuario puede registrarse con datos válidos', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'test@pawconnect.test',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ]);

    $response
        ->assertCreated()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token',
                'user' => ['id', 'name', 'email', 'role'],
            ],
        ]);

    expect($response->json('data.token'))->not->toBeEmpty();
    expect($response->json('data.user.email'))->toBe('test@pawconnect.test');

    $this->assertDatabaseHas('users', [
        'email' => 'test@pawconnect.test',
    ]);
});

test('el registro falla sin email', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Test',
        'password' => 'Password123',
    ])->assertUnprocessable()
      ->assertJsonValidationErrors(['email']);
});

test('el registro falla con email duplicado', function () {
    User::factory()->create(['email' => 'duplicate@test.com']);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Test',
        'email' => 'duplicate@test.com',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ])->assertUnprocessable()
      ->assertJsonValidationErrors(['email']);
});

// ── Login ─────────────────────────────────────────────────────────────────

test('un usuario puede iniciar sesión con credenciales válidas', function () {
    $user = User::factory()->create([
        'email' => 'login@pawconnect.test',
        'password' => bcrypt('Password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'login@pawconnect.test',
        'password' => 'Password123',
    ]);

    $response
        ->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token',
                'user' => ['id', 'name', 'email', 'role'],
            ],
        ]);

    expect($response->json('data.token'))->not->toBeEmpty();
});

test('el login falla con credenciales incorrectas', function () {
    User::factory()->create([
        'email' => 'user@test.com',
        'password' => bcrypt('CorrectPassword'),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'user@test.com',
        'password' => 'WrongPassword',
    ])->assertStatus(422); // Laravel devuelve 422 para credenciales incorrectas
});

test('el login falla con usuario inexistente', function () {
    $this->postJson('/api/v1/auth/login', [
        'email' => 'noexiste@test.com',
        'password' => 'Password123',
    ])->assertStatus(422); // Laravel devuelve 422 para credenciales incorrectas
});

// ── Logout ────────────────────────────────────────────────────────────────

test('un usuario autenticado puede cerrar sesión', function () {
    $user = actingAsUser();

    $response = $this->postJson('/api/v1/auth/logout');

    $response->assertOk();
    expect($response->json('message'))->toContain('Sesión cerrada');

    // El token debe estar revocado
    $this->getJson('/api/v1/users/me')->assertUnauthorized();
});

test('un usuario no autenticado no puede cerrar sesión', function () {
    $this->postJson('/api/v1/auth/logout')
        ->assertUnauthorized();
});
