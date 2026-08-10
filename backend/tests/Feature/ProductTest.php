<?php

use App\Models\Product;
use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Crear producto (solo admin) ──────────────────────────────────────────────────

test('un admin puede crear un producto', function () {
    $admin = actingAsAdmin();
    $provider = Provider::factory()->create();

    $response = $this->postJson('/api/v1/admin/products', [
        'provider_id' => $provider->id,
        'name' => 'Alimento Premium',
        'category' => 'comida',
        'description' => 'Alimento seco de alta calidad',
        'price' => 29.99,
        'stock' => 100,
        'min_weight_kg' => 10,
        'max_weight_kg' => 50,
        'recommended_breeds' => ['Labrador', 'Golden Retriever'],
        'photo' => 'https://example.com/product.jpg',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => ['id', 'name', 'price', 'stock', 'provider_id'],
        ]);

    expect($response->json('data.name'))->toBe('Alimento Premium');
    expect($response->json('data.category'))->toBe('comida');
    expect($response->json('data.stock'))->toEqual(100);

    $this->assertDatabaseHas('products', [
        'name' => 'Alimento Premium',
        'provider_id' => $provider->id,
    ]);
});

test('un usuario NO puede crear un producto', function () {
    $user = actingAsUser();
    $provider = Provider::factory()->create();

    $this->postJson('/api/v1/admin/products', [
        'provider_id' => $provider->id,
        'name' => 'Alimento Premium',
        'category' => 'comida',
        'price' => 29.99,
        'stock' => 100,
    ])->assertForbidden();
});

test('un usuario no autenticado NO puede crear un producto', function () {
    $provider = Provider::factory()->create();

    $this->postJson('/api/v1/admin/products', [
        'provider_id' => $provider->id,
        'name' => 'Alimento Premium',
        'category' => 'comida',
        'price' => 29.99,
        'stock' => 100,
    ])->assertUnauthorized();
});

test('validación: name es requerido', function () {
    $admin = actingAsAdmin();
    $provider = Provider::factory()->create();

    $response = $this->postJson('/api/v1/admin/products', [
        'provider_id' => $provider->id,
        'category' => 'comida',
        'price' => 29.99,
        'stock' => 100,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

test('validación: price debe ser positivo', function () {
    $admin = actingAsAdmin();
    $provider = Provider::factory()->create();

    $response = $this->postJson('/api/v1/admin/products', [
        'provider_id' => $provider->id,
        'name' => 'Producto',
        'category' => 'comida',
        'price' => -10,
        'stock' => 100,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('price');
});

// ── Listar productos ────────────────────────────────────────────────────────────

test('un usuario puede listar productos', function () {
    $user = actingAsUser();
    $provider = Provider::factory()->create();

    Product::factory()->count(5)->create(['provider_id' => $provider->id]);

    $response = $this->getJson('/api/v1/products');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'price', 'stock'],
            ],
            'pagination' => ['total', 'per_page', 'current_page', 'last_page'],
        ]);

    expect($response->json('pagination.total'))->toBeGreaterThanOrEqual(5);
});

test('un usuario no autenticado puede listar productos', function () {
    $provider = Provider::factory()->create();
    Product::factory()->count(3)->create(['provider_id' => $provider->id]);

    $response = $this->getJson('/api/v1/products');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'price'],
            ],
        ]);

    // Verificar que hay al menos 3 productos
    expect(count($response->json('data')))->toBeGreaterThanOrEqual(3);
});

test('la paginación de productos funciona correctamente', function () {
    $provider = Provider::factory()->create();
    Product::factory()->count(25)->create(['provider_id' => $provider->id]);

    $response = $this->getJson('/api/v1/products?page=2');

    $response->assertOk();
    expect($response->json('pagination.current_page'))->toEqual(2);
    
    // La página 2 debe tener datos (será 15 o menos dependiendo del total)
    expect($response->json('data'))->toBeTruthy();
});

// ── Ver producto individual ──────────────────────────────────────────────────────

test('un usuario puede ver un producto individual', function () {
    $user = actingAsUser();
    $provider = Provider::factory()->create();
    $product = Product::factory()->create(['provider_id' => $provider->id]);

    $response = $this->getJson("/api/v1/products/{$product->id}");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['id', 'name', 'price', 'stock', 'provider'],
        ]);

    expect($response->json('data.id'))->toEqual($product->id);
});

test('un usuario no autenticado puede ver un producto individual', function () {
    $provider = Provider::factory()->create();
    $product = Product::factory()->create(['provider_id' => $provider->id]);

    $response = $this->getJson("/api/v1/products/{$product->id}");

    $response->assertOk();
    expect($response->json('data.name'))->toBe($product->name);
});

test('un usuario obtiene 404 si el producto no existe', function () {
    $this->getJson('/api/v1/products/99999')->assertNotFound();
});

// ── Actualizar producto (solo admin) ─────────────────────────────────────────────

test('un admin puede actualizar un producto', function () {
    $admin = actingAsAdmin();
    $provider = Provider::factory()->create();
    $product = Product::factory()->create([
        'provider_id' => $provider->id,
        'name' => 'Nombre Original',
        'price' => 29.99,
    ]);

    $response = $this->putJson("/api/v1/admin/products/{$product->id}", [
        'name' => 'Nombre Actualizado',
        'price' => 39.99,
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['id', 'name', 'price'],
        ]);

    expect($response->json('data.name'))->toBe('Nombre Actualizado');
    expect($response->json('data.price'))->toEqual(39.99);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Nombre Actualizado',
        'price' => 39.99,
    ]);
});

test('un usuario NO puede actualizar un producto', function () {
    $user = actingAsUser();
    $provider = Provider::factory()->create();
    $product = Product::factory()->create(['provider_id' => $provider->id]);

    $this->putJson("/api/v1/admin/products/{$product->id}", [
        'name' => 'Nuevo Nombre',
    ])->assertForbidden();
});

test('validación en update: price debe ser positivo si se proporciona', function () {
    $admin = actingAsAdmin();
    $provider = Provider::factory()->create();
    $product = Product::factory()->create(['provider_id' => $provider->id]);

    $response = $this->putJson("/api/v1/admin/products/{$product->id}", [
        'price' => -5,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('price');
});

test('actualización parcial: solo actualiza campos proporcionados', function () {
    $admin = actingAsAdmin();
    $provider = Provider::factory()->create();
    $product = Product::factory()->create([
        'provider_id' => $provider->id,
        'name' => 'Original',
        'price' => 29.99,
        'stock' => 100,
    ]);

    $this->putJson("/api/v1/admin/products/{$product->id}", [
        'price' => 49.99,
    ]);

    $product->refresh();
    expect($product->name)->toBe('Original');
    expect($product->price)->toEqual(49.99);
    expect($product->stock)->toEqual(100);
});

// ── Eliminar producto (solo admin) ───────────────────────────────────────────────

test('un admin puede eliminar un producto', function () {
    $admin = actingAsAdmin();
    $provider = Provider::factory()->create();
    $product = Product::factory()->create(['provider_id' => $provider->id]);

    $response = $this->deleteJson("/api/v1/admin/products/{$product->id}");

    $response->assertOk()
        ->assertJson(['message' => 'Producto eliminado correctamente.']);

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

test('un usuario NO puede eliminar un producto', function () {
    $user = actingAsUser();
    $provider = Provider::factory()->create();
    $product = Product::factory()->create(['provider_id' => $provider->id]);

    $this->deleteJson("/api/v1/admin/products/{$product->id}")->assertForbidden();
});

test('eliminar producto inexistente retorna 404', function () {
    $admin = actingAsAdmin();

    $this->deleteJson('/api/v1/admin/products/99999')->assertNotFound();
});

// ── Campos del recurso ───────────────────────────────────────────────────────────

test('el recurso producto incluye información del proveedor', function () {
    $provider = Provider::factory()->create(['name' => 'Proveedor Test']);
    $product = Product::factory()->create(['provider_id' => $provider->id]);

    $response = $this->getJson("/api/v1/products/{$product->id}");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'provider' => ['id', 'name', 'email'],
            ],
        ]);

    expect($response->json('data.provider.name'))->toBe('Proveedor Test');
});

test('los campos numéricos se convierten correctamente', function () {
    $provider = Provider::factory()->create();
    $product = Product::factory()->create([
        'provider_id' => $provider->id,
        'price' => '29.99',
        'min_weight_kg' => '10.50',
        'max_weight_kg' => '50.75',
    ]);

    $response = $this->getJson("/api/v1/products/{$product->id}");

    $response->assertOk();
    expect($response->json('data.price'))->toEqual(29.99);
    expect($response->json('data.min_weight_kg'))->toEqual(10.50);
    expect($response->json('data.max_weight_kg'))->toEqual(50.75);
});

test('el campo recommended_breeds es un array', function () {
    $provider = Provider::factory()->create();
    $product = Product::factory()->create([
        'provider_id' => $provider->id,
        'recommended_breeds' => ['Labrador', 'Golden Retriever'],
    ]);

    $response = $this->getJson("/api/v1/products/{$product->id}");

    $response->assertOk();
    expect($response->json('data.recommended_breeds'))->toEqual(['Labrador', 'Golden Retriever']);
});
