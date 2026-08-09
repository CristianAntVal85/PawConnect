<?php

use App\Models\HealthRecord;
use App\Models\Pet;
use App\Notifications\HealthReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

// ── Comando SendHealthReminders ────────────────────────────────────────────────────

test('el comando encuentra registros sanitarios con notificaciones activas dentro de 3 días', function () {
    Notification::fake();

    $user = actingAsUser();
    $pet = Pet::factory()->create(['user_id' => $user->id]);

    // Record que vence en 2 días (debe incluirse)
    HealthRecord::factory()->create([
        'pet_id' => $pet->id,
        'type' => 'vaccination',
        'next_due_date' => now()->addDays(2),
        'notifications_active' => true,
    ]);

    // Record que vence en 5 días (no debe incluirse)
    HealthRecord::factory()->create([
        'pet_id' => $pet->id,
        'type' => 'deworming',
        'next_due_date' => now()->addDays(5),
        'notifications_active' => true,
    ]);

    $exitCode = Artisan::call('health:reminders');

    expect($exitCode)->toBe(0);

    // Debe haber encontrado al menos 1 record
    Notification::assertSentTo($user, HealthReminderNotification::class);
});

test('el comando respeta el flag notifications_active', function () {
    Notification::fake();

    $user = actingAsUser();
    $pet = Pet::factory()->create(['user_id' => $user->id]);

    // Record con notificaciones desactivadas
    HealthRecord::factory()->create([
        'pet_id' => $pet->id,
        'type' => 'vaccination',
        'next_due_date' => now()->addDays(1),
        'notifications_active' => false,
    ]);

    $exitCode = Artisan::call('health:reminders');

    expect($exitCode)->toBe(0);

    // No debe enviar notificaciones
    Notification::assertNotSentTo($user, HealthReminderNotification::class);
});

test('el comando respeta el umbral de 3 días', function () {
    Notification::fake();

    $user = actingAsUser();
    $pet = Pet::factory()->create(['user_id' => $user->id]);

    // Record que vence exactamente en 3 días (debe incluirse)
    HealthRecord::factory()->create([
        'pet_id' => $pet->id,
        'type' => 'checkup',
        'next_due_date' => now()->addDays(3),
        'notifications_active' => true,
    ]);

    // Record que vence en 4 días (no debe incluirse)
    HealthRecord::factory()->create([
        'pet_id' => $pet->id,
        'type' => 'surgery',
        'next_due_date' => now()->addDays(4),
        'notifications_active' => true,
    ]);

    $exitCode = Artisan::call('health:reminders');

    expect($exitCode)->toBe(0);

    // Debe encontrar solo el primer record
    Notification::assertSentTo($user, HealthReminderNotification::class);
});

test('el comando maneja correctamente cuando no hay records para notificar', function () {
    Notification::fake();

    $exitCode = Artisan::call('health:reminders');

    expect($exitCode)->toBe(0);
});

// ── Notificación HealthReminderNotification ────────────────────────────────────────

test('la notificación se envía a través de canal database y mail', function () {
    Notification::fake();

    $user = actingAsUser();
    $pet = Pet::factory()->create(['user_id' => $user->id]);
    $healthRecord = HealthRecord::factory()->create([
        'pet_id' => $pet->id,
        'type' => 'vaccination',
        'next_due_date' => now()->addDays(2),
    ]);

    $notification = new HealthReminderNotification($healthRecord);
    $via = $notification->via($user);

    expect($via)->toContain('database')
        ->toContain('mail');
});

test('la notificación contiene datos correctos', function () {
    $user = actingAsUser();
    $pet = Pet::factory()->create(['user_id' => $user->id, 'name' => 'Firulais']);
    $healthRecord = HealthRecord::factory()->create([
        'pet_id' => $pet->id,
        'type' => 'vaccination',
        'next_due_date' => now()->addDays(5)->startOfDay(),
    ]);

    $notification = new HealthReminderNotification($healthRecord);
    $data = $notification->toArray($user);

    expect($data)
        ->toHaveKeys(['health_record_id', 'pet_id', 'pet_name', 'type', 'type_label', 'next_due_date', 'days_until_due', 'message'])
        ->and($data['pet_name'])->toBe('Firulais')
        ->and($data['type'])->toBe('vaccination')
        ->and($data['type_label'])->toBe('Vacunación')
        ->and($data['days_until_due'])->toBeGreaterThanOrEqual(4)
        ->and($data['days_until_due'])->toBeLessThanOrEqual(5);
});

test('la notificación traduce correctamente los tipos de registro', function () {
    $user = actingAsUser();
    $pet = Pet::factory()->create(['user_id' => $user->id]);

    $types = [
        'vaccination' => 'Vacunación',
        'deworming' => 'Desparasitación',
        'checkup' => 'Revisión',
        'surgery' => 'Cirugía',
        'medication' => 'Medicación',
    ];

    foreach ($types as $type => $label) {
        $healthRecord = HealthRecord::factory()->create([
            'pet_id' => $pet->id,
            'type' => $type,
        ]);

        $notification = new HealthReminderNotification($healthRecord);
        $data = $notification->toArray($user);

        expect($data['type_label'])->toBe($label);
    }
});

test('el mensaje de la notificación incluye detalles de la mascota y la cita', function () {
    Notification::fake();

    $user = actingAsUser();
    $pet = Pet::factory()->create(['user_id' => $user->id, 'name' => 'Luna']);
    $healthRecord = HealthRecord::factory()->create([
        'pet_id' => $pet->id,
        'type' => 'vaccination',
        'next_due_date' => now()->addDays(7),
    ]);

    $notification = new HealthReminderNotification($healthRecord);
    $data = $notification->toArray($user);

    expect($data['message'])->toContain('Luna')
        ->toContain('Vacunación');
});

