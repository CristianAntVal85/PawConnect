<?php

use App\Models\PetFriendlyPlace;
use App\Models\PlaceReview;
use App\Models\User;
use Tests\TestCase;

class PlaceReviewTest extends TestCase
{
    private User $user;
    private User $otherUser;
    private User $admin;
    private PetFriendlyPlace $place;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'user']);
        $this->otherUser = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->place = PetFriendlyPlace::factory()->create([
            'status' => 'approved',
            'created_by' => $this->user->id,
        ]);
    }

    /** @test */
    public function usuario_autenticado_puede_crear_resena()
    {
        $data = [
            'rating' => 5,
            'body'   => 'Excelente lugar para llevar perros. Muy limpio y seguro.',
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('api.v1.places.reviews.store', $this->place->id), $data);

        $response->assertCreated();
        $response->assertJsonPath('data.rating', 5);
        $response->assertJsonPath('data.user.id', $this->user->id);

        $this->assertDatabaseHas('place_reviews', [
            'place_id' => $this->place->id,
            'user_id'  => $this->user->id,
            'rating'   => 5,
        ]);
    }

    /** @test */
    public function usuario_no_autenticado_no_puede_crear_resena()
    {
        $data = [
            'rating' => 5,
            'body'   => 'Una reseña genial',
        ];

        $response = $this->postJson(route('api.v1.places.reviews.store', $this->place->id), $data);

        $response->assertUnauthorized();
    }

    /** @test */
    public function validacion_rating_entre_1_y_5()
    {
        $invalidData = [
            'rating' => 6,
            'body'   => 'Una reseña',
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('api.v1.places.reviews.store', $this->place->id), $invalidData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('rating');
    }

    /** @test */
    public function validacion_body_requerido()
    {
        $data = [
            'rating' => 5,
            'body'   => '',
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('api.v1.places.reviews.store', $this->place->id), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('body');
    }

    /** @test */
    public function validacion_body_maximo_1000_caracteres()
    {
        $data = [
            'rating' => 5,
            'body'   => str_repeat('a', 1001),
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('api.v1.places.reviews.store', $this->place->id), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('body');
    }

    /** @test */
    public function usuario_puede_listar_resenas_de_un_lugar()
    {
        PlaceReview::factory()->count(3)->create([
            'place_id' => $this->place->id,
        ]);

        $response = $this->getJson(route('api.v1.places.reviews.index', $this->place->id));

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        $response->assertJsonPath('pagination.total', 3);
    }

    /** @test */
    public function resenas_se_listan_en_orden_descendente()
    {
        $review1 = PlaceReview::factory()->create(['place_id' => $this->place->id]);
        sleep(1); // Asegurar que hay diferencia en timestamps
        $review2 = PlaceReview::factory()->create(['place_id' => $this->place->id]);

        $response = $this->getJson(route('api.v1.places.reviews.index', $this->place->id));

        $response->assertOk();
        $data = $response->json('data');
        
        // La segunda reseña debe estar primero (más reciente)
        $this->assertEquals($review2->id, $data[0]['id']);
        $this->assertEquals($review1->id, $data[1]['id']);
    }

    /** @test */
    public function usuario_puede_ver_detalle_de_resena()
    {
        $review = PlaceReview::factory()->create([
            'place_id' => $this->place->id,
            'user_id'  => $this->user->id,
        ]);

        $response = $this->getJson(
            route('api.v1.places.reviews.show', [$this->place->id, $review->id])
        );

        $response->assertOk();
        $response->assertJsonPath('data.id', $review->id);
        $response->assertJsonPath('data.rating', $review->rating);
    }

    /** @test */
    public function usuario_no_puede_ver_resena_de_otro_lugar()
    {
        $otherPlace = PetFriendlyPlace::factory()->create();
        $review = PlaceReview::factory()->create(['place_id' => $this->place->id]);

        $response = $this->getJson(
            route('api.v1.places.reviews.show', [$otherPlace->id, $review->id])
        );

        $response->assertNotFound();
    }

    /** @test */
    public function autor_puede_actualizar_su_resena()
    {
        $review = PlaceReview::factory()->create([
            'place_id' => $this->place->id,
            'user_id'  => $this->user->id,
            'rating'   => 3,
        ]);

        $data = [
            'rating' => 5,
            'body'   => 'Actualicé mi opinión: es excelente',
        ];

        $response = $this->actingAs($this->user)
            ->putJson(
                route('api.v1.places.reviews.update', [$this->place->id, $review->id]),
                $data
            );

        $response->assertOk();
        $response->assertJsonPath('data.rating', 5);

        $this->assertDatabaseHas('place_reviews', [
            'id'     => $review->id,
            'rating' => 5,
        ]);
    }

    /** @test */
    public function usuario_no_puede_actualizar_resena_de_otro()
    {
        $review = PlaceReview::factory()->create([
            'place_id' => $this->place->id,
            'user_id'  => $this->user->id,
        ]);

        $data = ['rating' => 1];

        $response = $this->actingAs($this->otherUser)
            ->putJson(
                route('api.v1.places.reviews.update', [$this->place->id, $review->id]),
                $data
            );

        $response->assertForbidden();
    }

    /** @test */
    public function admin_puede_actualizar_cualquier_resena()
    {
        $review = PlaceReview::factory()->create([
            'place_id' => $this->place->id,
            'user_id'  => $this->user->id,
        ]);

        $data = ['rating' => 1];

        $response = $this->actingAs($this->admin)
            ->putJson(
                route('api.v1.places.reviews.update', [$this->place->id, $review->id]),
                $data
            );

        $response->assertOk();
    }

    /** @test */
    public function autor_puede_eliminar_su_resena()
    {
        $review = PlaceReview::factory()->create([
            'place_id' => $this->place->id,
            'user_id'  => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson(
                route('api.v1.places.reviews.destroy', [$this->place->id, $review->id])
            );

        $response->assertOk();

        $this->assertDatabaseMissing('place_reviews', [
            'id' => $review->id,
        ]);
    }

    /** @test */
    public function usuario_no_puede_eliminar_resena_de_otro()
    {
        $review = PlaceReview::factory()->create([
            'place_id' => $this->place->id,
            'user_id'  => $this->user->id,
        ]);

        $response = $this->actingAs($this->otherUser)
            ->deleteJson(
                route('api.v1.places.reviews.destroy', [$this->place->id, $review->id])
            );

        $response->assertForbidden();
    }

    /** @test */
    public function admin_puede_eliminar_cualquier_resena()
    {
        $review = PlaceReview::factory()->create([
            'place_id' => $this->place->id,
            'user_id'  => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson(
                route('api.v1.places.reviews.destroy', [$this->place->id, $review->id])
            );

        $response->assertOk();

        $this->assertDatabaseMissing('place_reviews', [
            'id' => $review->id,
        ]);
    }
}
