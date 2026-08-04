<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PetTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_pets_routes(): void
    {
        $this->getJson('/api/v1/pets')->assertUnauthorized();
    }

    public function test_authenticated_user_can_crud_own_pet(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $createResponse = $this->postJson('/api/v1/pets', [
            'species' => 'dog',
            'name' => 'Firulais',
            'available_for_walks' => true,
        ]);

        $createResponse->assertCreated();
        $petId = $createResponse->json('data.id');

        $this->assertDatabaseHas('pets', [
            'id' => $petId,
            'user_id' => $user->id,
            'name' => 'Firulais',
        ]);

        $this->putJson('/api/v1/pets/'.$petId, [
            'name' => 'Max',
        ])->assertOk();

        $this->assertDatabaseHas('pets', [
            'id' => $petId,
            'name' => 'Max',
        ]);

        $this->deleteJson('/api/v1/pets/'.$petId)->assertOk();

        $this->assertDatabaseMissing('pets', [
            'id' => $petId,
        ]);
    }

    public function test_policy_blocks_updating_other_users_pet(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $pet = Pet::factory()->create([
            'user_id' => $owner->id,
            'species' => 'dog',
            'name' => 'Rocky',
        ]);

        Sanctum::actingAs($intruder);

        $this->putJson('/api/v1/pets/'.$pet->id, [
            'name' => 'No permitido',
        ])->assertForbidden();

        $this->assertDatabaseHas('pets', [
            'id' => $pet->id,
            'name' => 'Rocky',
        ]);
    }
}
