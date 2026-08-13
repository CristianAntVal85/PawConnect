<?php

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Pet;
use App\Models\Product;
use App\Models\Provider;
use App\Models\User;
use App\Services\PaymentIntentData;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

// ────────────────────────────────────────────────────────────────
// Vistas Web
// ────────────────────────────────────────────────────────────────

test('usuario autenticado puede ver el checkout', function () {
    $user    = actingAsUser();
    $provider = Provider::factory()->create();
    $product = Product::factory()->create(['provider_id' => $provider->id, 'stock' => 10]);
    Pet::factory()->create(['user_id' => $user->id]);

    $response = $this->get(route('web.checkout', ['product_id' => $product->id]));

    $response->assertOk()
        ->assertViewIs('orders.checkout')
        ->assertViewHas('product')
        ->assertViewHas('pets');
});

test('checkout muestra mensaje si no hay producto', function () {
    actingAsUser();

    $response = $this->get(route('web.checkout'));

    $response->assertOk()
        ->assertViewHas('product', null);
});

test('usuario no autenticado es redirigido al login desde checkout', function () {
    $response = $this->get(route('web.checkout'));

    $response->assertRedirect(route('login'));
});

test('usuario puede ver mis pedidos', function () {
    actingAsUser();

    $response = $this->get(route('web.orders.index'));

    $response->assertOk()
        ->assertViewIs('orders.index');
});

test('usuario puede ver detalle de pedido', function () {
    actingAsUser();

    $response = $this->get(route('web.orders.show', 42));

    $response->assertOk()
        ->assertViewIs('orders.show')
        ->assertViewHas('orderId', 42);
});

// ────────────────────────────────────────────────────────────────
// API - Crear PaymentIntent
// ────────────────────────────────────────────────────────────────

test('crear payment-intent devuelve client_secret', function () {
    $user    = actingAsUser();
    $provider = Provider::factory()->create();
    $product = Product::factory()->create(['provider_id' => $provider->id, 'stock' => 10, 'price' => 50.00]);

    $this->mock(PaymentService::class, function (MockInterface $mock) {
        $mock->shouldReceive('createPaymentIntent')->once()
            ->andReturn(new PaymentIntentData('pi_test_xxx', 'pi_test_secret_xxx'));
    });

    $response = $this->postJson('/api/v1/orders/payment-intent', [
        'product_id' => $product->id,
        'quantity'   => 2,
    ]);

    $response->assertOk()
        ->assertJsonStructure(['success', 'message', 'data' => ['client_secret', 'amount', 'currency', 'product']])
        ->assertJson(['data' => ['amount' => 10000, 'currency' => 'usd', 'client_secret' => 'pi_test_secret_xxx']]);
});

test('payment-intent falla si stock insuficiente', function () {
    actingAsUser();
    $provider = Provider::factory()->create();
    $product = Product::factory()->create(['provider_id' => $provider->id, 'stock' => 1]);

    $response = $this->postJson('/api/v1/orders/payment-intent', [
        'product_id' => $product->id,
        'quantity'   => 5,
    ]);

    $response->assertUnprocessable()
        ->assertJsonFragment(['message' => 'Stock insuficiente. Solo hay 1 unidades disponibles.']);
});

test('payment-intent requiere autenticación', function () {
    $response = $this->postJson('/api/v1/orders/payment-intent', [
        'product_id' => 1,
        'quantity'   => 1,
    ]);

    $response->assertUnauthorized();
});

// ────────────────────────────────────────────────────────────────
// API - Crear Pedido
// ────────────────────────────────────────────────────────────────

test('crear pedido sin stripe_payment_id funciona en desarrollo', function () {
    $user    = actingAsUser();
    $provider = Provider::factory()->create();
    $product = Product::factory()->create(['provider_id' => $provider->id, 'stock' => 10, 'price' => 25.00]);
    $pet     = Pet::factory()->create(['user_id' => $user->id]);

    $response = $this->postJson('/api/v1/orders', [
        'product_id'       => $product->id,
        'pet_id'           => $pet->id,
        'quantity'         => 2,
        'shipping_address' => 'Calle Mayor 1, Madrid',
        'payment_method'   => 'card',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'message',
            'data' => ['id', 'status', 'total_price', 'quantity', 'product', 'pet', 'status_history'],
        ])
        ->assertJson(['data' => ['status' => 'pending', 'total_price' => 50.00, 'quantity' => 2]]);

    // Verificar que se creó el historial de estado
    $this->assertDatabaseHas('order_status_history', [
        'status' => 'pending',
        'note'   => 'Pedido creado correctamente.',
    ]);
});

test('crear pedido reduce el stock del producto', function () {
    $user    = actingAsUser();
    $provider = Provider::factory()->create();
    $product = Product::factory()->create(['provider_id' => $provider->id, 'stock' => 10]);
    $pet     = Pet::factory()->create(['user_id' => $user->id]);

    $this->postJson('/api/v1/orders', [
        'product_id'       => $product->id,
        'pet_id'           => $pet->id,
        'quantity'         => 3,
        'shipping_address' => 'Calle Mayor 1',
    ]);

    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 7]);
});

test('no se puede pedir más de lo que hay en stock', function () {
    $user    = actingAsUser();
    $provider = Provider::factory()->create();
    $product = Product::factory()->create(['provider_id' => $provider->id, 'stock' => 2]);
    $pet     = Pet::factory()->create(['user_id' => $user->id]);

    $response = $this->postJson('/api/v1/orders', [
        'product_id'       => $product->id,
        'pet_id'           => $pet->id,
        'quantity'         => 5,
        'shipping_address' => 'Calle Mayor 1',
    ]);

    $response->assertUnprocessable()
        ->assertJsonFragment(['message' => 'Stock insuficiente. Solo hay 2 unidades disponibles.']);

    // El stock no debe haber cambiado
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 2]);
});

test('no se puede crear pedido con mascota ajena', function () {
    $owner   = User::factory()->create();
    $intruder = actingAsUser();
    $provider = Provider::factory()->create();
    $product = Product::factory()->create(['provider_id' => $provider->id, 'stock' => 5]);
    $pet     = Pet::factory()->create(['user_id' => $owner->id]);

    $response = $this->postJson('/api/v1/orders', [
        'product_id'       => $product->id,
        'pet_id'           => $pet->id,
        'quantity'         => 1,
        'shipping_address' => 'Calle Mayor 1',
    ]);

    $response->assertForbidden();
});

test('usuario no autenticado no puede crear pedido', function () {
    $response = $this->postJson('/api/v1/orders', [
        'product_id'       => 1,
        'pet_id'           => 1,
        'quantity'         => 1,
        'shipping_address' => 'Calle Mayor 1',
    ]);

    $response->assertUnauthorized();
});

test('stripe_payment_id se guarda en el pedido', function () {
    $user    = actingAsUser();
    $provider = Provider::factory()->create();
    $product = Product::factory()->create(['provider_id' => $provider->id, 'stock' => 5]);
    $pet     = Pet::factory()->create(['user_id' => $user->id]);

    // Mock del PaymentService para que is_payment_succeeded devuelva true
    $this->mock(PaymentService::class, function (MockInterface $mock) {
        $mock->shouldReceive('isPaymentSucceeded')->once()->with('pi_test_abc')->andReturn(true);
    });

    $this->postJson('/api/v1/orders', [
        'product_id'        => $product->id,
        'pet_id'            => $pet->id,
        'quantity'          => 1,
        'shipping_address'  => 'Calle Mayor 1',
        'stripe_payment_id' => 'pi_test_abc',
    ]);

    $this->assertDatabaseHas('orders', ['stripe_payment_id' => 'pi_test_abc']);
});

test('pago no confirmado por stripe rechaza la creación del pedido', function () {
    $user    = actingAsUser();
    $provider = Provider::factory()->create();
    $product = Product::factory()->create(['provider_id' => $provider->id, 'stock' => 5]);
    $pet     = Pet::factory()->create(['user_id' => $user->id]);

    $this->mock(PaymentService::class, function (MockInterface $mock) {
        $mock->shouldReceive('isPaymentSucceeded')->once()->andReturn(false);
    });

    $response = $this->postJson('/api/v1/orders', [
        'product_id'        => $product->id,
        'pet_id'            => $pet->id,
        'quantity'          => 1,
        'shipping_address'  => 'Calle Mayor 1',
        'stripe_payment_id' => 'pi_test_failed',
    ]);

    $response->assertUnprocessable()
        ->assertJsonFragment(['message' => 'El pago no ha sido confirmado. Por favor, completa el pago antes de crear el pedido.']);

    $this->assertDatabaseMissing('orders', ['stripe_payment_id' => 'pi_test_failed']);
});

// ────────────────────────────────────────────────────────────────
// API - Listar Pedidos
// ────────────────────────────────────────────────────────────────

test('usuario ve solo sus propios pedidos', function () {
    $user     = actingAsUser();
    $other    = User::factory()->create();
    $provider  = Provider::factory()->create();

    Order::factory()->count(3)->create(['user_id' => $user->id]);
    Order::factory()->count(5)->create(['user_id' => $other->id]);

    $response = $this->getJson('/api/v1/orders');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('admin ve todos los pedidos', function () {
    actingAsAdmin();
    $provider = Provider::factory()->create();

    Order::factory()->count(8)->create();

    $response = $this->getJson('/api/v1/orders');

    $response->assertOk()
        ->assertJson(['pagination' => ['total' => 8]]);
});

test('listado de pedidos está paginado', function () {
    $user    = actingAsUser();
    $provider = Provider::factory()->create();

    Order::factory()->count(20)->create(['user_id' => $user->id]);

    $response = $this->getJson('/api/v1/orders?page=2');

    $response->assertOk()
        ->assertJson(['pagination' => ['current_page' => 2]]);
});

// ────────────────────────────────────────────────────────────────
// API - Ver Pedido Individual
// ────────────────────────────────────────────────────────────────

test('usuario puede ver su propio pedido con historial', function () {
    $user  = actingAsUser();
    $order = Order::factory()->create(['user_id' => $user->id]);
    OrderStatusHistory::factory()->create(['order_id' => $order->id, 'status' => 'pending', 'note' => 'Creado']);

    $response = $this->getJson("/api/v1/orders/{$order->id}");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['id', 'status', 'total_price', 'status_history' => [['status', 'note', 'created_at']]],
        ]);
});

test('usuario no puede ver pedidos ajenos', function () {
    $owner   = User::factory()->create();
    actingAsUser();
    $order   = Order::factory()->create(['user_id' => $owner->id]);

    $response = $this->getJson("/api/v1/orders/{$order->id}");

    $response->assertForbidden();
});

test('admin puede ver cualquier pedido', function () {
    actingAsAdmin();
    $order = Order::factory()->create();

    $response = $this->getJson("/api/v1/orders/{$order->id}");

    $response->assertOk()
        ->assertJson(['data' => ['id' => $order->id]]);
});

test('pedido inexistente devuelve 404', function () {
    actingAsUser();

    $response = $this->getJson('/api/v1/orders/99999');

    $response->assertNotFound();
});
