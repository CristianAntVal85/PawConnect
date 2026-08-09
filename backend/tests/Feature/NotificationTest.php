<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;

uses(RefreshDatabase::class);

// ── Endpoint GET /api/v1/notifications ────────────────────────────────────────

test('un usuario puede obtener sus notificaciones no leídas', function () {
    $user = actingAsUser();

    // Crear algunas notificaciones
    DatabaseNotification::create([
        'id' => 'notif-1',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'type' => 'App\\Notifications\\HealthReminderNotification',
        'data' => json_encode([
            'type' => 'App\\Notifications\\HealthReminderNotification',
            'pet_name' => 'Firulais',
            'pet_id' => 1,
            'message' => 'Recordatorio de vacunación',
            'next_due_date' => now()->addDays(2)->toDateString(),
            'days_until_due' => 2,
        ]),
        'read_at' => null,
    ]);

    DatabaseNotification::create([
        'id' => 'notif-2',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'type' => 'App\\Notifications\\HealthReminderNotification',
        'data' => json_encode([
            'type' => 'App\\Notifications\\HealthReminderNotification',
            'pet_name' => 'Luna',
            'pet_id' => 2,
            'message' => 'Recordatorio de desparasitación',
            'next_due_date' => now()->addDays(1)->toDateString(),
            'days_until_due' => 1,
        ]),
        'read_at' => null,
    ]);

    $response = $this->getJson('/api/v1/notifications');

    $response->assertOk()
        ->assertJsonStructure([
            'message',
            'data' => [
                'data' => [
                    '*' => ['id', 'type', 'data', 'read_at', 'created_at'],
                ],
            ],
            'unread_count',
        ])
        ->assertJsonPath('unread_count', 2);
});

test('un usuario no autenticado no puede acceder a notificaciones', function () {
    $this->getJson('/api/v1/notifications')
        ->assertUnauthorized();
});

test('la paginación funciona correctamente', function () {
    $user = actingAsUser();

    // Crear 25 notificaciones
    for ($i = 0; $i < 25; $i++) {
        DatabaseNotification::create([
            'id' => "notif-{$i}",
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'App\\Notifications\\HealthReminderNotification',
            'data' => json_encode([
                'message' => "Notificación {$i}",
            ]),
            'read_at' => null,
        ]);
    }

    $response = $this->getJson('/api/v1/notifications');

    $response->assertOk()
        ->assertJsonStructure(['data' => ['data', 'total', 'per_page']]);

    // Laravel pagina 20 por defecto, verificamos que hay datos
    expect($response->json('data.total'))->toBe(25)
        ->and(count($response->json('data.data')))->toBeLessThanOrEqual(25);
});

// ── Endpoint GET /api/v1/notifications/unread-count ───────────────────────────

test('obtener el contador de notificaciones no leídas', function () {
    $user = actingAsUser();

    DatabaseNotification::create([
        'id' => 'notif-1',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'type' => 'App\\Notifications\\HealthReminderNotification',
        'data' => json_encode(['message' => 'Test']),
        'read_at' => null,
    ]);

    DatabaseNotification::create([
        'id' => 'notif-2',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'type' => 'App\\Notifications\\HealthReminderNotification',
        'data' => json_encode(['message' => 'Test']),
        'read_at' => null,
    ]);

    $response = $this->getJson('/api/v1/notifications/unread-count');

    $response->assertOk()
        ->assertJsonPath('unread_count', 2);
});

test('el contador no incluye notificaciones leídas', function () {
    $user = actingAsUser();

    DatabaseNotification::create([
        'id' => 'notif-1',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'type' => 'App\\Notifications\\HealthReminderNotification',
        'data' => json_encode(['message' => 'Test']),
        'read_at' => null,
    ]);

    DatabaseNotification::create([
        'id' => 'notif-2',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'type' => 'App\\Notifications\\HealthReminderNotification',
        'data' => json_encode(['message' => 'Test']),
        'read_at' => now(),
    ]);

    $response = $this->getJson('/api/v1/notifications/unread-count');

    $response->assertOk()
        ->assertJsonPath('unread_count', 1);
});

// ── Endpoint PUT /api/v1/notifications/{id}/mark-as-read ──────────────────────

test('un usuario puede marcar una notificación como leída', function () {
    $user = actingAsUser();

    $notification = DatabaseNotification::create([
        'id' => 'notif-1',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'type' => 'App\\Notifications\\HealthReminderNotification',
        'data' => json_encode(['message' => 'Test']),
        'read_at' => null,
    ]);

    $response = $this->putJson("/api/v1/notifications/{$notification->id}/mark-as-read");

    $response->assertOk()
        ->assertJsonPath('message', 'Notificación marcada como leída.');

    $notification->refresh();
    expect($notification->read_at)->not->toBeNull();
});

test('un usuario no puede marcar notificaciones de otros usuarios como leídas', function () {
    $user = actingAsUser();
    $otherUser = User::factory()->create();

    $notification = DatabaseNotification::create([
        'id' => 'notif-1',
        'notifiable_type' => User::class,
        'notifiable_id' => $otherUser->id,
        'type' => 'App\\Notifications\\HealthReminderNotification',
        'data' => json_encode(['message' => 'Test']),
        'read_at' => null,
    ]);

    $response = $this->putJson("/api/v1/notifications/{$notification->id}/mark-as-read");

    $response->assertNotFound();
});

test('marcar como leída devuelve error si notificación no existe', function () {
    actingAsUser();

    $response = $this->putJson('/api/v1/notifications/notif-inexistente/mark-as-read');

    $response->assertNotFound();
});

// ── Notificaciones en cronograma (health reminders) ───────────────────────────

test('las notificaciones de salud incluyen datos correctos', function () {
    $user = actingAsUser();

    DatabaseNotification::create([
        'id' => 'health-notif',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'type' => 'App\\Notifications\\HealthReminderNotification',
        'data' => json_encode([
            'type' => 'App\\Notifications\\HealthReminderNotification',
            'pet_name' => 'Firulais',
            'pet_id' => 1,
            'type_label' => 'Vacunación',
            'message' => 'Próxima Vacunación de Firulais: 15/08/2026',
            'next_due_date' => '2026-08-15',
            'days_until_due' => 7,
        ]),
        'read_at' => null,
    ]);

    $response = $this->getJson('/api/v1/notifications');

    $response->assertOk();
    
    // El campo data es una string JSON, necesitamos decodificarlo
    $data = json_decode($response->json('data.data.0.data'), true);
    expect($data['pet_name'])->toBe('Firulais')
        ->and($data['type_label'])->toBe('Vacunación');
});
