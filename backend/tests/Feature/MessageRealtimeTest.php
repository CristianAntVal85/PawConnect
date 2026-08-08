<?php

use App\Events\MessageSent;
use App\Models\Pet;
use App\Models\PetMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('un participante del match puede enviar mensajes y se emite el evento', function () {
    $sender = User::factory()->create();
    $receiver = User::factory()->create();

    $senderPet = Pet::factory()->create([
        'user_id' => $sender->id,
        'species' => 'dog',
    ]);

    $receiverPet = Pet::factory()->create([
        'user_id' => $receiver->id,
        'species' => 'dog',
    ]);

    $match = PetMatch::query()->create([
        'pet_a_id' => $senderPet->id,
        'pet_b_id' => $receiverPet->id,
        'status' => 'matched',
        'matched_at' => now(),
    ]);

    Sanctum::actingAs($sender);
    Event::fake([MessageSent::class]);

    $response = $this->postJson('/api/v1/matches/'.$match->id.'/messages', [
        'body' => 'Hola desde el chat en tiempo real',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.match_id', $match->id)
        ->assertJsonPath('data.sender_id', $sender->id)
        ->assertJsonPath('data.body', 'Hola desde el chat en tiempo real');

    $this->assertDatabaseHas('messages', [
        'match_id' => $match->id,
        'sender_id' => $sender->id,
        'body' => 'Hola desde el chat en tiempo real',
    ]);

    Event::assertDispatched(MessageSent::class, function (MessageSent $event) use ($match, $sender) {
        return $event->message->match_id === $match->id
            && $event->message->sender_id === $sender->id;
    });
});

test('un usuario que no pertenece al match no puede enviar mensajes', function () {
    $petAUser = User::factory()->create();
    $petBUser = User::factory()->create();
    $intruder = User::factory()->create();

    $petA = Pet::factory()->create([
        'user_id' => $petAUser->id,
        'species' => 'dog',
    ]);

    $petB = Pet::factory()->create([
        'user_id' => $petBUser->id,
        'species' => 'dog',
    ]);

    $match = PetMatch::query()->create([
        'pet_a_id' => $petA->id,
        'pet_b_id' => $petB->id,
        'status' => 'matched',
        'matched_at' => now(),
    ]);

    Sanctum::actingAs($intruder);
    Event::fake([MessageSent::class]);

    $this->postJson('/api/v1/matches/'.$match->id.'/messages', [
        'body' => 'No deberia enviarse',
    ])->assertForbidden();

    $this->assertDatabaseCount('messages', 0);
    Event::assertNotDispatched(MessageSent::class);
});

test('al abrir una conversacion se marcan como leidos los mensajes recibidos', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $userPet = Pet::factory()->create([
        'user_id' => $user->id,
        'species' => 'dog',
    ]);

    $otherPet = Pet::factory()->create([
        'user_id' => $otherUser->id,
        'species' => 'dog',
    ]);

    $match = PetMatch::query()->create([
        'pet_a_id' => $userPet->id,
        'pet_b_id' => $otherPet->id,
        'status' => 'matched',
        'matched_at' => now(),
    ]);

    $incoming = $match->messages()->create([
        'sender_id' => $otherUser->id,
        'body' => 'Mensaje pendiente de lectura',
    ]);

    $own = $match->messages()->create([
        'sender_id' => $user->id,
        'body' => 'Mensaje propio',
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/matches/'.$match->id.'/messages')
        ->assertOk()
        ->assertJsonPath('data.0.id', $incoming->id)
        ->assertJsonPath('data.1.id', $own->id);

    expect($incoming->fresh()->read_at)->not->toBeNull();
    expect($own->fresh()->read_at)->toBeNull();
});
