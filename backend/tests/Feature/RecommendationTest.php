<?php

use App\Models\HealthRecord;
use App\Models\Pet;
use App\Models\Product;
use App\Models\Provider;
use App\Services\RecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Recomendaciones básicas ──────────────────────────────────────────────────────

test('recomienda productos basados en la especie de la mascota', function () {
    $user = actingAsUser();
    $provider = Provider::factory()->create();
    
    $dog = Pet::factory()->create([
        'user_id' => $user->id,
        'species' => 'dog',
        'weight' => 20,
    ]);

    // Productos para perros (sin restricciones de peso)
    Product::factory()->count(3)->create([
        'provider_id' => $provider->id,
        'category' => 'dog food',
        'min_weight_kg' => null,
        'max_weight_kg' => null,
        'stock' => 10,
    ]);

    // Productos para gatos (no deberían aparecer)
    Product::factory()->count(2)->create([
        'provider_id' => $provider->id,
        'category' => 'cat food',
        'min_weight_kg' => null,
        'max_weight_kg' => null,
        'stock' => 10,
    ]);

    $service = new RecommendationService();
    $recommendations = $service->recommendForPet($dog);

    expect($recommendations->total())->toEqual(3);
});

test('filtra productos por rango de peso', function () {
    $user = actingAsUser();
    $provider = Provider::factory()->create();
    
    $smallDog = Pet::factory()->create([
        'user_id' => $user->id,
        'species' => 'dog',
        'weight' => 5,
    ]);

    // Producto para perros pequeños (0-10kg)
    $smallProduct = Product::factory()->create([
        'provider_id' => $provider->id,
        'category' => 'comida dog',
        'min_weight_kg' => 0,
        'max_weight_kg' => 10,
        'stock' => 10,
    ]);

    // Producto para perros grandes (20-50kg) - no debería aparecer
    Product::factory()->create([
        'provider_id' => $provider->id,
        'category' => 'comida dog',
        'min_weight_kg' => 20,
        'max_weight_kg' => 50,
        'stock' => 10,
    ]);

    $service = new RecommendationService();
    $recommendations = $service->recommendForPet($smallDog);

    expect($recommendations->total())->toBeGreaterThanOrEqual(1);
    
    $ids = collect($recommendations->items())->pluck('id')->toArray();
    expect($ids)->toContain($smallProduct->id);
});

test('incluye productos sin restricción de peso', function () {
    $user = actingAsUser();
    $provider = Provider::factory()->create();
    
    $dog = Pet::factory()->create([
        'user_id' => $user->id,
        'species' => 'dog',
        'weight' => 15,
    ]);

    // Producto sin restricción de peso
    $universalProduct = Product::factory()->create([
        'provider_id' => $provider->id,
        'category' => 'juguetes dog',
        'min_weight_kg' => null,
        'max_weight_kg' => null,
        'stock' => 10,
    ]);

    $service = new RecommendationService();
    $recommendations = $service->recommendForPet($dog);

    expect($recommendations->total())->toBeGreaterThanOrEqual(1);
});

test('prioriza productos con raza recomendada', function () {
    $user = actingAsUser();
    $provider = Provider::factory()->create();
    
    $labrador = Pet::factory()->create([
        'user_id' => $user->id,
        'species' => 'dog',
        'breed' => 'Labrador',
        'weight' => 25,
    ]);

    // Producto recomendado para Labrador
    $recommendedProduct = Product::factory()->create([
        'provider_id' => $provider->id,
        'category' => 'dog premium',
        'min_weight_kg' => null,
        'max_weight_kg' => null,
        'recommended_breeds' => ['Labrador', 'Golden'],
        'stock' => 10,
    ]);

    // Producto genérico
    Product::factory()->create([
        'provider_id' => $provider->id,
        'category' => 'dog premium',
        'min_weight_kg' => null,
        'max_weight_kg' => null,
        'recommended_breeds' => null,
        'stock' => 10,
    ]);

    $service = new RecommendationService();
    $recommendations = $service->recommendForPet($labrador);

    // El primer resultado debería ser el recomendado para la raza
    expect($recommendations->items()[0]->id)->toEqual($recommendedProduct->id);
});

test('excluye productos sin stock', function () {
    $user = actingAsUser();
    $provider = Provider::factory()->create();
    
    $dog = Pet::factory()->create([
        'user_id' => $user->id,
        'species' => 'dog',
        'weight' => 20,
    ]);

    // Producto con stock
    Product::factory()->create([
        'provider_id' => $provider->id,
        'category' => 'dog accesorio',
        'min_weight_kg' => null,
        'max_weight_kg' => null,
        'stock' => 10,
    ]);

    // Producto sin stock
    Product::factory()->create([
        'provider_id' => $provider->id,
        'category' => 'dog accesorio',
        'min_weight_kg' => null,
        'max_weight_kg' => null,
        'stock' => 0,
    ]);

    $service = new RecommendationService();
    $recommendations = $service->recommendForPet($dog);

    expect($recommendations->total())->toEqual(1);
});

test('prioriza productos de salud si hay registros sanitarios próximos', function () {
    $user = actingAsUser();
    $provider = Provider::factory()->create();
    
    $dog = Pet::factory()->create([
        'user_id' => $user->id,
        'species' => 'dog',
        'weight' => 20,
    ]);

    // Registro sanitario próximo a vencer
    HealthRecord::factory()->create([
        'pet_id' => $dog->id,
        'type' => 'vaccination',
        'notifications_active' => true,
        'next_due_date' => now()->addDays(5),
    ]);

    // Producto de salud
    $healthProduct = Product::factory()->create([
        'provider_id' => $provider->id,
        'category' => 'salud dog',
        'min_weight_kg' => null,
        'max_weight_kg' => null,
        'stock' => 10,
    ]);

    // Producto de comida
    Product::factory()->create([
        'provider_id' => $provider->id,
        'category' => 'dog comida',
        'min_weight_kg' => null,
        'max_weight_kg' => null,
        'stock' => 10,
    ]);

    $service = new RecommendationService();
    $recommendations = $service->recommendForPet($dog);

    // El producto de salud debería estar en las recomendaciones
    $ids = collect($recommendations->items())->pluck('id')->toArray();
    expect($ids)->toContain($healthProduct->id);
});

test('la paginación funciona correctamente', function () {
    $user = actingAsUser();
    $provider = Provider::factory()->create();
    
    $dog = Pet::factory()->create([
        'user_id' => $user->id,
        'species' => 'dog',
        'weight' => 20,
    ]);

    Product::factory()->count(25)->create([
        'provider_id' => $provider->id,
        'category' => 'dog alimento',
        'min_weight_kg' => null,
        'max_weight_kg' => null,
        'stock' => 10,
    ]);

    $service = new RecommendationService();
    $recommendations = $service->recommendForPet($dog, 10);

    expect($recommendations->perPage())->toEqual(10);
    expect($recommendations->total())->toBeGreaterThanOrEqual(25);
    expect($recommendations->lastPage())->toBeGreaterThanOrEqual(3);
});

// ── Endpoint de recomendaciones ──────────────────────────────────────────────────

test('un usuario puede obtener recomendaciones para su mascota', function () {
    $user = actingAsUser();
    $provider = Provider::factory()->create();
    
    $pet = Pet::factory()->create([
        'user_id' => $user->id,
        'species' => 'dog',
        'name' => 'Max',
        'weight' => 15,
    ]);

    Product::factory()->count(5)->create([
        'provider_id' => $provider->id,
        'category' => 'dog nutricion',
        'min_weight_kg' => null,
        'max_weight_kg' => null,
        'stock' => 10,
    ]);

    $response = $this->getJson("/api/v1/products/recommended/{$pet->id}");

    $response->assertOk()
        ->assertJsonStructure([
            'message',
            'pet' => ['id', 'name', 'species', 'breed', 'age', 'weight'],
            'data' => [
                '*' => ['id', 'name', 'category', 'price', 'stock'],
            ],
            'pagination',
        ]);

    expect($response->json('pet.name'))->toBe('Max');
    expect($response->json('data'))->toHaveCount(5);
});

test('un usuario NO puede obtener recomendaciones de mascotas ajenas', function () {
    $owner = \App\Models\User::factory()->create();
    $intruder = actingAsUser();
    
    $pet = Pet::factory()->create(['user_id' => $owner->id]);

    $response = $this->getJson("/api/v1/products/recommended/{$pet->id}");

    $response->assertForbidden()
        ->assertJson([
            'message' => 'No tienes permiso para ver recomendaciones de esta mascota.',
        ]);
});

test('usuario no autenticado no puede obtener recomendaciones', function () {
    $user = \App\Models\User::factory()->create();
    $pet = Pet::factory()->create(['user_id' => $user->id]);

    $response = $this->getJson("/api/v1/products/recommended/{$pet->id}");

    $response->assertUnauthorized();
});

test('el endpoint retorna 404 si la mascota no existe', function () {
    actingAsUser();

    $response = $this->getJson('/api/v1/products/recommended/99999');

    $response->assertNotFound();
});

test('el parámetro per_page funciona en el endpoint', function () {
    $user = actingAsUser();
    $provider = Provider::factory()->create();
    
    $pet = Pet::factory()->create([
        'user_id' => $user->id,
        'species' => 'dog',
    ]);

    Product::factory()->count(30)->create([
        'provider_id' => $provider->id,
        'category' => 'dog premium',
        'min_weight_kg' => null,
        'max_weight_kg' => null,
        'stock' => 10,
    ]);

    $response = $this->getJson("/api/v1/products/recommended/{$pet->id}?per_page=5");

    $response->assertOk();
    expect($response->json('pagination.per_page'))->toEqual(5);
    expect($response->json('data'))->toHaveCount(5);
});

test('recomienda productos multiespecie para cualquier mascota', function () {
    $user = actingAsUser();
    $provider = Provider::factory()->create();
    
    $rabbit = Pet::factory()->create([
        'user_id' => $user->id,
        'species' => 'rabbit',
        'weight' => 2,
    ]);

    // Producto multiespecie sin restricciones de peso
    Product::factory()->create([
        'provider_id' => $provider->id,
        'category' => 'multiespecie',
        'min_weight_kg' => null,
        'max_weight_kg' => null,
        'stock' => 10,
    ]);

    // Producto general sin restricciones de peso
    Product::factory()->create([
        'provider_id' => $provider->id,
        'category' => 'general',
        'min_weight_kg' => null,
        'max_weight_kg' => null,
        'stock' => 10,
    ]);

    $service = new RecommendationService();
    $recommendations = $service->recommendForPet($rabbit);

    expect($recommendations->total())->toBeGreaterThanOrEqual(2);
});
