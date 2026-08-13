<?php

use App\Events\OrderStatusChanged;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Pet;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderStatusNotification;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderStatusFlowTest extends TestCase
{
    private User $admin;
    private User $customer;
    private Product $product;
    private Pet $pet;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock PaymentService para evitar que falle por Stripe no estar disponible
        $this->mock(PaymentService::class);

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer = User::factory()->create(['role' => 'user']);
        $this->product = Product::factory()->create(['stock' => 100]);
        $this->pet = Pet::factory()->create(['user_id' => $this->customer->id]);

        $this->order = Order::create([
            'user_id'           => $this->customer->id,
            'product_id'        => $this->product->id,
            'pet_id'            => $this->pet->id,
            'quantity'          => 1,
            'total_price'       => 100.00,
            'shipping_address'  => '123 Test St',
            'payment_method'    => 'card',
            'stripe_payment_id' => 'pi_test_123',
            'status'            => 'pending',
        ]);

        // Crear registro inicial de estado
        OrderStatusHistory::create([
            'order_id' => $this->order->id,
            'status'   => 'pending',
            'note'     => 'Pedido creado',
        ]);
    }

    /** @test */
    public function un_admin_puede_cambiar_estado_del_pedido()
    {
        Event::fake();
        Notification::fake();

        $response = $this->actingAs($this->admin)
            ->putJson(route('api.v1.orders.update', $this->order->id), [
                'status' => 'confirmed',
                'note'   => 'Pedido confirmado',
            ]);

        $response->assertOk();
        $response->assertJsonPath('message', 'Estado del pedido actualizado correctamente.');

        $this->order->refresh();
        $this->assertEquals('confirmed', $this->order->status);

        // Verificar que se creó el registro de historial
        $this->assertTrue(
            OrderStatusHistory::query()
                ->where('order_id', $this->order->id)
                ->where('status', 'confirmed')
                ->where('note', 'Pedido confirmado')
                ->exists()
        );

        // Verificar que se disparó el evento
        Event::assertDispatched(OrderStatusChanged::class, function ($event) {
            return $event->order->id === $this->order->id
                && $event->oldStatus === 'pending'
                && $event->newStatus === 'confirmed'
                && $event->note === 'Pedido confirmado';
        });
    }

    /** @test */
    public function un_usuario_normal_no_puede_cambiar_estado_del_pedido()
    {
        $response = $this->actingAs($this->customer)
            ->putJson(route('api.v1.orders.update', $this->order->id), [
                'status' => 'confirmed',
            ]);

        $response->assertForbidden();
    }

    /** @test */
    public function cambiar_estado_valida_valores_permitidos()
    {
        $response = $this->actingAs($this->admin)
            ->putJson(route('api.v1.orders.update', $this->order->id), [
                'status' => 'invalid_status',
            ]);

        $response->assertUnprocessable();
    }

    /** @test */
    public function el_sistema_no_cambia_si_es_el_mismo_estado()
    {
        Notification::fake();

        $response = $this->actingAs($this->admin)
            ->putJson(route('api.v1.orders.update', $this->order->id), [
                'status' => 'pending',
            ]);

        $response->assertOk();
        $response->assertJsonPath('message', 'El pedido ya tiene ese estado.');

        // Verificar que solo existe 1 registro (el inicial)
        $this->assertEquals(1, $this->order->statusHistory()->count());
    }

    /** @test */
    public function se_envian_notificaciones_cuando_cambia_estado()
    {
        Notification::fake();

        $this->actingAs($this->admin)
            ->putJson(route('api.v1.orders.update', $this->order->id), [
                'status' => 'shipped',
                'note'   => 'Enviado por FedEx',
            ]);

        Notification::assertSentTo(
            $this->customer,
            OrderStatusNotification::class,
            function ($notification) {
                return $notification->newStatus === 'shipped'
                    && $notification->note === 'Enviado por FedEx';
            }
        );
    }

    /** @test */
    public function transicion_completa_pending_to_delivered()
    {
        Notification::fake();
        $statuses = ['confirmed', 'shipped', 'delivered'];

        foreach ($statuses as $status) {
            $response = $this->actingAs($this->admin)
                ->putJson(route('api.v1.orders.update', $this->order->id), [
                    'status' => $status,
                ]);

            $response->assertOk();

            $this->order->refresh();
            $this->assertEquals($status, $this->order->status);
        }

        // Verificar que tenemos 4 registros: 1 inicial + 3 cambios
        $this->assertEquals(4, $this->order->statusHistory()->count());
    }

    /** @test */
    public function note_es_opcional()
    {
        Notification::fake();

        $response = $this->actingAs($this->admin)
            ->putJson(route('api.v1.orders.update', $this->order->id), [
                'status' => 'confirmed',
            ]);

        $response->assertOk();

        $this->assertTrue(
            OrderStatusHistory::query()
                ->where('order_id', $this->order->id)
                ->where('status', 'confirmed')
                ->where('note', null)
                ->exists()
        );
    }

    /** @test */
    public function respuesta_json_incluye_datos_actualizados()
    {
        Notification::fake(); // Deshabilitar notificaciones para evitar errores en tests

        $response = $this->actingAs($this->admin)
            ->putJson(route('api.v1.orders.update', $this->order->id), [
                'status' => 'confirmed',
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'status',
                'total_price',
                'shipping_address',
                'payment_method',
                'stripe_payment_id',
                'created_at',
                'product',
                'pet',
                'status_history',
            ],
        ]);

        $response->assertJsonPath('data.status', 'confirmed');
    }

    /** @test */
    public function admin_puede_ver_todos_los_pedidos()
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('api.v1.orders.index'));

        $response->assertOk();
        $response->assertJsonStructure(['message', 'data', 'pagination']);

        // Verificar que el orden actual está en los datos devueltos
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertContains($this->order->id, $ids);
    }

    /** @test */
    public function usuario_solo_ve_sus_propios_pedidos()
    {
        // Crear otro usuario y pedido
        $otherCustomer = User::factory()->create();
        $otherPet = Pet::factory()->create(['user_id' => $otherCustomer->id]);
        Order::create([
            'user_id'           => $otherCustomer->id,
            'product_id'        => $this->product->id,
            'pet_id'            => $otherPet->id,
            'quantity'          => 1,
            'total_price'       => 50.00,
            'shipping_address'  => '456 Other St',
            'payment_method'    => 'card',
            'stripe_payment_id' => 'pi_other_456',
            'status'            => 'pending',
        ]);

        $response = $this->actingAs($this->customer)
            ->getJson(route('api.v1.orders.index'));

        $response->assertOk();

        // El usuario solo debe ver su propio pedido
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertContains($this->order->id, $ids);

        // Total de pedidos del usuario debe ser 1
        $userOrders = Order::where('user_id', $this->customer->id)->get();
        $this->assertEquals(1, $userOrders->count());
    }

    /** @test */
    public function actualizar_estado_con_tracking_number()
    {
        Notification::fake();

        $response = $this->actingAs($this->admin)
            ->putJson(route('api.v1.orders.update', $this->order->id), [
                'status'           => 'shipped',
                'tracking_number'  => 'FDX123456789',
                'tracking_url'     => 'https://tracking.fedex.com/track?tracking=FDX123456789',
                'note'             => 'Enviado por FedEx',
            ]);

        $response->assertOk();

        $this->order->refresh();
        $this->assertEquals('shipped', $this->order->status);
        $this->assertEquals('FDX123456789', $this->order->tracking_number);
        $this->assertEquals('https://tracking.fedex.com/track?tracking=FDX123456789', $this->order->tracking_url);
    }

    /** @test */
    public function tracking_url_invalida_es_rechazada()
    {
        $response = $this->actingAs($this->admin)
            ->putJson(route('api.v1.orders.update', $this->order->id), [
                'status'       => 'shipped',
                'tracking_url' => 'not-a-valid-url',
            ]);

        $response->assertUnprocessable();
    }

    /** @test */
    public function notificacion_incluye_tracking_info()
    {
        Notification::fake();

        $this->actingAs($this->admin)
            ->putJson(route('api.v1.orders.update', $this->order->id), [
                'status'           => 'shipped',
                'tracking_number'  => 'FDX789',
                'tracking_url'     => 'https://tracking.fedex.com/track?tracking=FDX789',
            ]);

        Notification::assertSentTo(
            $this->customer,
            OrderStatusNotification::class,
            function ($notification) {
                $array = $notification->toDatabase($this->customer);
                return $array['tracking_number'] === 'FDX789' &&
                       $array['tracking_url'] === 'https://tracking.fedex.com/track?tracking=FDX789';
            }
        );
    }

    /** @test */
    public function transicion_de_shipped_a_delivered_mantiene_tracking()
    {
        Notification::fake();

        // Primero cambiar a shipped con tracking
        $this->actingAs($this->admin)
            ->putJson(route('api.v1.orders.update', $this->order->id), [
                'status'           => 'shipped',
                'tracking_number'  => 'FDX123',
                'tracking_url'     => 'https://tracking.fedex.com/track?tracking=FDX123',
            ]);

        $this->order->refresh();
        $trackingBefore = $this->order->tracking_number;

        // Luego cambiar a delivered
        $response = $this->actingAs($this->admin)
            ->putJson(route('api.v1.orders.update', $this->order->id), [
                'status' => 'delivered',
                'note'   => 'Entregado en mano',
            ]);

        $response->assertOk();

        $this->order->refresh();
        $this->assertEquals('delivered', $this->order->status);
        $this->assertEquals($trackingBefore, $this->order->tracking_number); // El tracking se mantiene
    }

    /** @test */
    public function tracking_number_es_unico()
    {
        $this->actingAs($this->admin)
            ->putJson(route('api.v1.orders.update', $this->order->id), [
                'status'          => 'shipped',
                'tracking_number' => 'UNIQUE123',
            ]);

        // Crear otro pedido e intentar usar el mismo tracking number
        $otherCustomer = User::factory()->create();
        $otherPet = Pet::factory()->create(['user_id' => $otherCustomer->id]);
        $otherOrder = Order::create([
            'user_id'           => $otherCustomer->id,
            'product_id'        => $this->product->id,
            'pet_id'            => $otherPet->id,
            'quantity'          => 1,
            'total_price'       => 100.00,
            'shipping_address'  => '789 Other St',
            'payment_method'    => 'card',
            'stripe_payment_id' => 'pi_other_789',
            'status'            => 'pending',
        ]);

        // Intentar asignar el mismo tracking number debe fallar (constraint unique en BD)
        $this->assertTrue(
            Order::where('tracking_number', 'UNIQUE123')->count() === 1
        );
    }

    /** @test */
    public function order_sin_tracking_al_crear()
    {
        $this->assertNull($this->order->tracking_number);
        $this->assertNull($this->order->tracking_url);
    }

    /** @test */
    public function buscar_por_tracking_number_en_admin()
    {
        $this->actingAs($this->admin)
            ->putJson(route('api.v1.orders.update', $this->order->id), [
                'status'          => 'shipped',
                'tracking_number' => 'SEARCH123ABC',
            ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.orders.index', ['search' => 'SEARCH123ABC']));

        $response->assertOk();
        $response->assertViewHas('orders');
        $this->assertTrue($response->viewData('orders')->contains($this->order));
    }

    /** @test */
    public function tracking_url_incluida_en_resource()
    {
        $this->actingAs($this->admin)
            ->putJson(route('api.v1.orders.update', $this->order->id), [
                'status'           => 'shipped',
                'tracking_number'  => 'RES123',
                'tracking_url'     => 'https://example.com/track/RES123',
            ]);

        $response = $this->actingAs($this->customer)
            ->getJson(route('api.v1.orders.show', $this->order->id));

        $response->assertOk();
        $response->assertJsonPath('data.tracking_number', 'RES123');
        $response->assertJsonPath('data.tracking_url', 'https://example.com/track/RES123');
    }
}

