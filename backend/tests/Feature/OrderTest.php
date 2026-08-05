<?php

use App\Models\Order;
use App\Models\Pet;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Crear pedido ──────────────────────────────────────────────────────────────────

test('un usuario puede crear un pedido', function () {
    $user = actingAsUser();
    $pet = Pet::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create(['price' => 25.50, 'stock' => 10]);

    $response = $this->postJson('/api/v1/orders', [
        'product_id' => $product->id,
        'pet_id' => $pet->id,
        'quantity' => 2,
        'shipping_address' => 'Calle Falsa 123, Madrid',
        'payment_method' => 'card',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => ['id', 'total_price', 'status', 'product_id', 'pet_id'],
        ]);

    expect($response->json('data.total_price'))->toBe(51.0); // 25.50 * 2
    expect($response->json('data.status'))->toBe('pending');

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'total_price' => 51.0,
    ]);
});

test('el total_price se calcula automáticamente', function () {
    $user = actingAsUser();
    $pet = Pet::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create(['price' => 10.00]);

    $response = $this->postJson('/api/v1/orders', [
        'product_id' => $product->id,
        'pet_id' => $pet->id,
        'quantity' => 5,
        'shipping_address' => 'Dirección test',
        'payment_method' => 'card',
    ]);

    expect($response->json('data.total_price'))->toBe(50.0);
});

test('un usuario NO puede crear pedidos con mascotas de otros usuarios', function () {
    $owner = User::factory()->create();
    $intruder = actingAsUser();

    $pet = Pet::factory()->create(['user_id' => $owner->id]);
    $product = Product::factory()->create();

    $this->postJson('/api/v1/orders', [
        'product_id' => $product->id,
        'pet_id' => $pet->id,
        'quantity' => 1,
        'shipping_address' => 'Test',
        'payment_method' => 'card',
    ])->assertForbidden();
});

// ── Listar pedidos ───────────────────────────────────────────────────────────────

test('un usuario puede listar solo sus propios pedidos', function () {
    $user = actingAsUser();
    $otherUser = User::factory()->create();

    Order::factory()->count(3)->create(['user_id' => $user->id]);
    Order::factory()->count(2)->create(['user_id' => $otherUser->id]);

    $response = $this->getJson('/api/v1/orders');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('la lista de pedidos incluye información del producto', function () {
    $user = actingAsUser();
    $product = Product::factory()->create(['name' => 'Producto Test']);
    
    Order::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);

    $response = $this->getJson('/api/v1/orders');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'product_id', 'pet_id', 'total_price', 'status'],
            ],
        ]);
});

// ── Ver detalle de pedido ───────────────────────────────────────────────────────

test('un usuario puede ver el detalle de su propio pedido', function () {
    $user = actingAsUser();
    $order = Order::factory()->create(['user_id' => $user->id]);

    $response = $this->getJson("/api/v1/orders/{$order->id}");

    $response->assertOk()
        ->assertJson([
            'data' => ['id' => $order->id],
        ]);
});

test('un usuario NO puede ver pedidos de otros usuarios', function () {
    $owner = User::factory()->create();
    $intruder = actingAsUser();

    $order = Order::factory()->create(['user_id' => $owner->id]);

    $this->getJson("/api/v1/orders/{$order->id}")->assertForbidden();
});

// ── Validaciones ──────────────────────────────────────────────────────────────────

test('la creación falla sin campos requeridos', function () {
    actingAsUser();

    $this->postJson('/api/v1/orders', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['product_id', 'pet_id', 'quantity', 'shipping_address']);
});

test('la cantidad debe ser al menos 1', function () {
    $user = actingAsUser();
    $pet = Pet::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create();

    $this->postJson('/api/v1/orders', [
        'product_id' => $product->id,
        'pet_id' => $pet->id,
        'quantity' => 0,
        'shipping_address' => 'Test',
    ])->assertUnprocessable()
      ->assertJsonValidationErrors(['quantity']);
});

test('el producto debe existir', function () {
    $user = actingAsUser();
    $pet = Pet::factory()->create(['user_id' => $user->id]);

    $this->postJson('/api/v1/orders', [
        'product_id' => 99999, // No existe
        'pet_id' => $pet->id,
        'quantity' => 1,
        'shipping_address' => 'Test',
    ])->assertUnprocessable()
      ->assertJsonValidationErrors(['product_id']);
});

// ── Estados de pedido ────────────────────────────────────────────────────────────

test('un pedido nuevo tiene status pending por defecto', function () {
    $user = actingAsUser();
    $pet = Pet::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create();

    $response = $this->postJson('/api/v1/orders', [
        'product_id' => $product->id,
        'pet_id' => $pet->id,
        'quantity' => 1,
        'shipping_address' => 'Test',
        'payment_method' => 'card',
    ]);

    expect($response->json('data.status'))->toBe('pending');
});
