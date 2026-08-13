<?php

use App\Http\Controllers\Web\Admin\ReviewController;
use App\Models\PlaceReview;
use App\Models\PetFriendlyPlace;
use App\Models\User;
use App\Notifications\ReviewStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('Review Moderation - Admin', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);

        $this->place = PetFriendlyPlace::factory()->create([
            'status' => 'approved',
            'created_by' => $this->user->id,
        ]);

        $this->reviewPending = PlaceReview::factory()->create([
            'place_id' => $this->place->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'rating' => 4,
        ]);

        $this->reviewApproved = PlaceReview::factory()->create([
            'place_id' => $this->place->id,
            'user_id' => $this->user->id,
            'status' => 'approved',
            'rating' => 5,
        ]);
    });

    it('solo admin puede ver la lista de reseñas', function () {
        actingAs($this->user);
        get(route('admin.reviews.index'))->assertStatus(403);

        actingAs($this->admin);
        get(route('admin.reviews.index'))->assertStatus(200);
    });

    it('admin puede aprobar una reseña pendiente', function () {
        Notification::fake();

        $request = Request::create(route('admin.reviews.approve', $this->reviewPending), 'POST', []);
        $request->setUserResolver(fn () => $this->admin);

        $response = app(ReviewController::class)->approve($request, $this->reviewPending);

        expect($response->status())->toBe(302);
        $this->reviewPending->refresh();
        expect($this->reviewPending->status)->toBe('approved');
        expect($this->reviewPending->rejection_reason)->toBeNull();
        Notification::assertSentTo($this->user, ReviewStatusNotification::class);
    });

    it('admin puede rechazar una reseña pendiente con motivo', function () {
        Notification::fake();

        $request = Request::create(route('admin.reviews.reject', $this->reviewPending), 'POST', [
            'rejection_reason' => 'Contenido inapropiado',
        ]);
        $request->setUserResolver(fn () => $this->admin);

        $response = app(ReviewController::class)->reject($request, $this->reviewPending);

        expect($response->status())->toBe(302);
        $this->reviewPending->refresh();
        expect($this->reviewPending->status)->toBe('rejected');
        expect($this->reviewPending->rejection_reason)->toBe('Contenido inapropiado');
        Notification::assertSentTo($this->user, ReviewStatusNotification::class);
    });

    it('rechazar una reseña requiere motivo', function () {
        $request = Request::create(route('admin.reviews.reject', $this->reviewPending), 'POST', [
            'rejection_reason' => '',
        ]);
        $request->setUserResolver(fn () => $this->admin);

        try {
            app(ReviewController::class)->reject($request, $this->reviewPending);
            throw new RuntimeException('Expected validation exception was not raised.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            expect($e->errors())->toHaveKey('rejection_reason');
        }

        expect($this->reviewPending->refresh()->status)->toBe('pending');
    });

    it('no puede aprobar una reseña ya aprobada', function () {
        $request = Request::create(route('admin.reviews.approve', $this->reviewApproved), 'POST', []);
        $request->setUserResolver(fn () => $this->admin);

        $response = app(ReviewController::class)->approve($request, $this->reviewApproved);

        expect($response->status())->toBe(302);
        $this->reviewApproved->refresh();
        expect($this->reviewApproved->status)->toBe('approved');
    });

    it('no puede rechazar una reseña ya rechazada', function () {
        $rejected = PlaceReview::factory()->create([
            'place_id' => $this->place->id,
            'user_id' => $this->user->id,
            'status' => 'rejected',
            'rejection_reason' => 'Spam',
        ]);

        $request = Request::create(route('admin.reviews.reject', $rejected), 'POST', [
            'rejection_reason' => 'Otra razón',
        ]);
        $request->setUserResolver(fn () => $this->admin);

        $response = app(ReviewController::class)->reject($request, $rejected);

        expect($response->status())->toBe(302);
        $rejected->refresh();
        expect($rejected->status)->toBe('rejected');
        expect($rejected->rejection_reason)->toBe('Spam');
    });

    it('admin puede ver el detalle de una reseña', function () {
        actingAs($this->admin);
        $response = get(route('admin.reviews.show', $this->reviewPending));

        $response->assertStatus(200)
            ->assertSee($this->reviewPending->body)
            ->assertSee($this->user->name)
            ->assertSee($this->place->name);
    });

    it('filtra reseñas por estado', function () {
        actingAs($this->admin);

        $response = get(route('admin.reviews.index', ['status' => 'pending']));
        $response->assertStatus(200);
        $response->assertSee($this->place->name);
        $response->assertSee($this->reviewPending->user->name);

        $response = get(route('admin.reviews.index', ['status' => 'approved']));
        $response->assertStatus(200);
        $response->assertSee($this->reviewApproved->user->name);
    });

    it('busca reseñas por lugar o usuario', function () {
        actingAs($this->admin);

        $response = get(route('admin.reviews.index', ['search' => $this->place->name]));
        $response->assertStatus(200);
        $response->assertSee($this->place->name);

        $response = get(route('admin.reviews.index', ['search' => $this->user->name]));
        $response->assertStatus(200);
        $response->assertSee($this->user->name);
    });

    it('filtra reseñas por calificación', function () {
        actingAs($this->admin);

        $response = get(route('admin.reviews.index', ['rating' => '4']));
        $response->assertStatus(200);
        $response->assertViewHas('reviews');

        $response = get(route('admin.reviews.index', ['rating' => '5']));
        $response->assertStatus(200);
        $response->assertViewHas('reviews');
    });
});
