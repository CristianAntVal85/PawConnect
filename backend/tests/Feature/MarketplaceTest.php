<?php

use App\Models\Pet;
use App\Models\Product;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ────────────────────────────────────────────────────────────────
// Vista Index - Catálogo General
// ────────────────────────────────────────────────────────────────

test('usuario autenticado puede ver el marketplace', function () {
    $user = actingAsUser();

    $response = $this->get(route('web.marketplace.index'));

    $response->assertOk()
        ->assertViewIs('marketplace.index')
        ->assertViewHas('pets')
        ->assertViewHas('categories');
});

test('usuario no autenticado es redirigido a login', function () {
    $response = $this->get(route('web.marketplace.index'));

    $response->assertRedirect(route('login'));
});

test('usuario ve solo sus mascotas perros en selector de recomendaciones', function () {
    $user = actingAsUser();
    
    // Crear mascotas del usuario
    $dog = Pet::factory()->create(['user_id' => $user->id, 'species' => 'dog']);
    $cat = Pet::factory()->create(['user_id' => $user->id, 'species' => 'cat']);
    $rabbit = Pet::factory()->create(['user_id' => $user->id, 'species' => 'rabbit']);

    $response = $this->get(route('web.marketplace.index'));

    $response->assertOk();
    expect($response->viewData('pets'))->toHaveCount(1)
        ->and($response->viewData('pets')[0]->id)->toBe($dog->id);
});

test('usuario sin mascotas ve mensaje educativo', function () {
    actingAsUser();

    $response = $this->get(route('web.marketplace.index'));

    $response->assertOk()
        ->assertSeeText('Crea tu primera mascota');
});

test('categorías disponibles se pasan a la vista', function () {
    actingAsUser();

    $response = $this->get(route('web.marketplace.index'));

    $categories = $response->viewData('categories');
    expect($categories)->toHaveKeys(['comida', 'accesorios', 'juguetes', 'higiene', 'estetica', 'salud', 'multiespecie']);
});

// ────────────────────────────────────────────────────────────────
// Vista Show - Detalle del Producto
// ────────────────────────────────────────────────────────────────

test('usuario puede ver detalle de un producto', function () {
    actingAsUser();
    $provider = Provider::factory()->create();
    $product = Product::factory()->create(['provider_id' => $provider->id, 'id' => 42]);

    $response = $this->get(route('web.marketplace.show', ['product' => 42]));

    $response->assertOk()
        ->assertViewIs('marketplace.show')
        ->assertViewHas('productId', 42);
});

test('detalle de producto carga via JavaScript', function () {
    actingAsUser();
    $provider = Provider::factory()->create();
    $product = Product::factory()->create(['provider_id' => $provider->id]);

    $response = $this->get(route('web.marketplace.show', ['product' => $product->id]));

    $response->assertOk()
        ->assertSeeText('Cargando detalles del producto');
});

test('usuario no autenticado no puede ver detalle', function () {
    $response = $this->get(route('web.marketplace.show', ['product' => 1]));

    $response->assertRedirect(route('login'));
});

// ────────────────────────────────────────────────────────────────
// API - Catálogo de Productos
// ────────────────────────────────────────────────────────────────

test('endpoint catálogo devuelve productos paginados', function () {
    $provider = Provider::factory()->create();
    Product::factory()->count(25)->create(['provider_id' => $provider->id, 'stock' => 10]);

    $response = $this->getJson('/api/v1/products');

    $response->assertOk()
        ->assertJsonStructure([
            'message',
            'data' => ['*' => ['id', 'name', 'price', 'stock', 'category']],
            'pagination',
        ])
        ->assertJsonCount(20, 'data')
        ->assertJson(['pagination' => ['total' => 25, 'per_page' => 20, 'current_page' => 1, 'last_page' => 2]]);
});

test('filtro by_category devuelve solo productos de esa categoría', function () {
    $provider = Provider::factory()->create();
    Product::factory()->count(5)->create(['provider_id' => $provider->id, 'category' => 'comida']);
    Product::factory()->count(3)->create(['provider_id' => $provider->id, 'category' => 'juguetes']);

    $response = $this->getJson('/api/v1/products?category=comida');

    $response->assertOk()
        ->assertJsonCount(5, 'data')
        ->assertJson(['pagination' => ['total' => 5]]);
});

test('filtro by_price devuelve solo productos hasta ese precio', function () {
    $provider = Provider::factory()->create();
    Product::factory()->count(3)->create(['provider_id' => $provider->id, 'price' => 50.00]);
    Product::factory()->count(2)->create(['provider_id' => $provider->id, 'price' => 150.00]);

    $response = $this->getJson('/api/v1/products?max_price=100');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('filtro in_stock devuelve solo productos disponibles', function () {
    $provider = Provider::factory()->create();
    Product::factory()->count(4)->create(['provider_id' => $provider->id, 'stock' => 10]);
    Product::factory()->count(2)->create(['provider_id' => $provider->id, 'stock' => 0]);

    $response = $this->getJson('/api/v1/products?in_stock=true');

    $response->assertOk()
        ->assertJsonCount(4, 'data');
});

test('combinar filtros: categoría + precio + stock', function () {
    $provider = Provider::factory()->create();
    
    // Producto que cumple todos los criterios
    Product::factory()->create([
        'provider_id' => $provider->id,
        'category' => 'comida',
        'price' => 50.00,
        'stock' => 5,
    ]);
    
    // Productos que no cumplen
    Product::factory()->create(['provider_id' => $provider->id, 'category' => 'juguetes', 'price' => 50.00, 'stock' => 5]);
    Product::factory()->create(['provider_id' => $provider->id, 'category' => 'comida', 'price' => 150.00, 'stock' => 5]);
    Product::factory()->create(['provider_id' => $provider->id, 'category' => 'comida', 'price' => 50.00, 'stock' => 0]);

    $response = $this->getJson('/api/v1/products?category=comida&max_price=100&in_stock=true');

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

test('paginación respeta page parameter', function () {
    $provider = Provider::factory()->create();
    Product::factory()->count(25)->create(['provider_id' => $provider->id]);

    $response = $this->getJson('/api/v1/products?page=2');

    $response->assertOk()
        ->assertJsonCount(5, 'data')
        ->assertJson(['pagination' => ['current_page' => 2]]);
});

// ────────────────────────────────────────────────────────────────
// API - Recomendaciones Personalizadas
// ────────────────────────────────────────────────────────────────

test('usuario obtiene recomendaciones para su mascota perro', function () {
    $user = actingAsUser();
    $provider = Provider::factory()->create();
    
    $dog = Pet::factory()->create([
        'user_id' => $user->id,
        'species' => 'dog',
        'weight' => 20,
    ]);

    Product::factory()->count(3)->create([
        'provider_id' => $provider->id,
        'category' => 'dog alimento',
        'stock' => 10,
    ]);

    $response = $this->getJson("/api/v1/products/recommended/{$dog->id}");

    $response->assertOk()
        ->assertJsonStructure([
            'message',
            'pet' => ['id', 'name', 'species', 'weight'],
            'data' => ['*' => ['id', 'name', 'price']],
            'pagination',
        ])
        ->assertJsonCount(3, 'data');
});

test('usuario NO puede ver recomendaciones de mascota ajena', function () {
    $owner = User::factory()->create();
    $intruder = actingAsUser();
    
    $dog = Pet::factory()->create(['user_id' => $owner->id, 'species' => 'dog']);

    $response = $this->getJson("/api/v1/products/recommended/{$dog->id}");

    $response->assertForbidden();
});

test('usuario no autenticado no puede acceder a recomendaciones', function () {
    $dog = Pet::factory()->create(['species' => 'dog']);

    $response = $this->getJson("/api/v1/products/recommended/{$dog->id}");

    $response->assertUnauthorized();
});

test('recomendaciones para mascota no existente retorna 404', function () {
    actingAsUser();

    $response = $this->getJson('/api/v1/products/recommended/99999');

    $response->assertNotFound();
});

test('recomendaciones respetan paginación', function () {
    $user = actingAsUser();
    $provider = Provider::factory()->create();
    
    $dog = Pet::factory()->create(['user_id' => $user->id, 'species' => 'dog']);

    Product::factory()->count(25)->create([
        'provider_id' => $provider->id,
        'category' => 'dog comida',
        'stock' => 10,
    ]);

    $response = $this->getJson("/api/v1/products/recommended/{$dog->id}?page=2");

    $response->assertOk()
        ->assertJsonCount(5, 'data')
        ->assertJson(['pagination' => ['current_page' => 2]]);
});

// ────────────────────────────────────────────────────────────────
// API - Detalle de Producto
// ────────────────────────────────────────────────────────────────

test('usuario puede ver un producto individual', function () {
    $provider = Provider::factory()->create();
    $product = Product::factory()->create([
        'provider_id' => $provider->id,
        'name' => 'Alimento Premium',
        'price' => 49.99,
    ]);

    $response = $this->getJson("/api/v1/products/{$product->id}");

    $response->assertOk()
        ->assertJsonStructure([
            'message',
            'data' => [
                'id', 'name', 'price', 'category', 'stock',
                'provider' => ['id', 'name'],
            ],
        ])
        ->assertJson([
            'data' => [
                'id' => $product->id,
                'name' => 'Alimento Premium',
                'price' => 49.99,
            ],
        ]);
});

test('producto no existente retorna 404', function () {
    $response = $this->getJson('/api/v1/products/99999');

    $response->assertNotFound();
});

// ────────────────────────────────────────────────────────────────
// Casos Edge
// ────────────────────────────────────────────────────────────────

test('catálogo vacío devuelve array vacío', function () {
    // No crear productos

    $response = $this->getJson('/api/v1/products');

    $response->assertOk()
        ->assertJsonCount(0, 'data')
        ->assertJson(['pagination' => ['total' => 0]]);
});

test('recomendaciones para mascota sin productos devuelve array vacío', function () {
    $user = actingAsUser();
    $dog = Pet::factory()->create(['user_id' => $user->id, 'species' => 'dog']);
    
    // No crear productos

    $response = $this->getJson("/api/v1/products/recommended/{$dog->id}");

    $response->assertOk()
        ->assertJsonCount(0, 'data');
});

test('filtro por raza busca en recommended_breeds', function () {
    $provider = Provider::factory()->create();
    
    $product1 = Product::factory()->create([
        'provider_id' => $provider->id,
        'recommended_breeds' => ['Labrador', 'Golden Retriever'],
    ]);
    
    $product2 = Product::factory()->create([
        'provider_id' => $provider->id,
        'recommended_breeds' => ['Bulldog'],
    ]);

    $response = $this->getJson('/api/v1/products?breed=Labrador');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJson(['data' => [['id' => $product1->id]]]);
});

test('producto sin foto muestra dato null', function () {
    $provider = Provider::factory()->create();
    $product = Product::factory()->create(['provider_id' => $provider->id, 'photo' => null]);

    $response = $this->getJson("/api/v1/products/{$product->id}");

    $response->assertOk()
        ->assertJson(['data' => ['photo' => null]]);
});

test('recomendaciones incluyen información de la mascota', function () {
    $user = actingAsUser();
    $provider = Provider::factory()->create();
    
    $dog = Pet::factory()->create([
        'user_id' => $user->id,
        'species' => 'dog',
        'name' => 'Max',
        'breed' => 'Labrador',
        'age' => 3,
        'weight' => 25,
    ]);

    Product::factory()->create(['provider_id' => $provider->id, 'category' => 'dog comida']);

    $response = $this->getJson("/api/v1/products/recommended/{$dog->id}");

    $response->assertOk()
        ->assertJson([
            'pet' => [
                'id' => $dog->id,
                'name' => 'Max',
                'breed' => 'Labrador',
                'age' => 3,
                'weight' => 25.00,
            ],
        ]);
});
