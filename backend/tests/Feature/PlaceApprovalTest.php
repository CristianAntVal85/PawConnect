<?php

use App\Http\Controllers\Web\Admin\PlaceController;
use App\Models\PetFriendlyPlace;
use App\Models\User;
use App\Notifications\PlaceStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('Place Management - Admin Approval/Rejection', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);

        $this->placePending = PetFriendlyPlace::factory()->create([
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);
    });

    it('solo admin puede ver la lista de lugares', function () {
        actingAs($this->user);
        get(route('admin.places.index'))->assertStatus(403);

        actingAs($this->admin);
        get(route('admin.places.index'))->assertStatus(200);
    });

    it('admin puede aprobar un lugar pendiente', function () {
        Notification::fake();

        $request = Request::create(route('admin.places.approve', $this->placePending), 'POST', []);
        $request->setUserResolver(fn () => $this->admin);

        $response = app(PlaceController::class)->approve($request, $this->placePending);

        expect($response->status())->toBe(302);
        $this->placePending->refresh();
        expect($this->placePending->status)->toBe('approved');
        Notification::assertSentTo($this->user, PlaceStatusNotification::class);
    });

    it('admin puede rechazar un lugar pendiente con motivo', function () {
        Notification::fake();

        $request = Request::create(route('admin.places.reject', $this->placePending), 'POST', [
            'rejection_reason' => 'Ubicación incorrecta',
        ]);
        $request->setUserResolver(fn () => $this->admin);

        $response = app(PlaceController::class)->reject($request, $this->placePending);

        expect($response->status())->toBe(302);
        $this->placePending->refresh();
        expect($this->placePending->status)->toBe('rejected');
        Notification::assertSentTo($this->user, PlaceStatusNotification::class);
    });

    it('rechazar un lugar requiere motivo', function () {
        $request = Request::create(route('admin.places.reject', $this->placePending), 'POST', [
            'rejection_reason' => '',
        ]);
        $request->setUserResolver(fn () => $this->admin);

        try {
            app(PlaceController::class)->reject($request, $this->placePending);
            throw new RuntimeException('Expected validation exception was not raised.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            expect($e->errors())->toHaveKey('rejection_reason');
        }

        expect($this->placePending->refresh()->status)->toBe('pending');
    });

    it('admin puede ver el detalle del lugar y su creador', function () {
        actingAs($this->admin);
        $response = get(route('admin.places.show', $this->placePending));

        $response->assertStatus(200)
            ->assertSee($this->placePending->name)
            ->assertSee($this->user->name);
    });
});
