<?php

use App\Models\User;
use App\Models\Pet;
use App\Models\Product;
use App\Models\Order;
use App\Models\PetFriendlyPlace;
use App\Models\PlaceReview;
use App\Exports\UsersExport;
use App\Exports\PetsExport;
use App\Exports\ProductsExport;
use App\Exports\OrdersExport;
use App\Exports\PlacesExport;
use App\Exports\ReviewsExport;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('CSV Export Functionality', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);

        $this->pet = Pet::factory()->create(['user_id' => $this->user->id]);
        $this->place = PetFriendlyPlace::factory()->create(['created_by' => $this->user->id]);
        $this->review = PlaceReview::factory()->create([
            'place_id' => $this->place->id,
            'user_id' => $this->user->id,
            'status' => 'approved'
        ]);
    });

    it('users export puede descargarse', function () {
        $response = actingAs($this->admin)->get(route('admin.users.export'));

        expect($response->headers->get('content-disposition'))->toContain('attachment');
        expect($response->headers->get('content-disposition'))->toContain('.csv');
    });

    it('pets export puede descargarse', function () {
        $response = actingAs($this->admin)->get(route('admin.pets.export'));

        expect($response->headers->get('content-disposition'))->toContain('attachment');
        expect($response->headers->get('content-disposition'))->toContain('.csv');
    });

    it('products export puede descargarse', function () {
        $response = actingAs($this->admin)->get(route('admin.products.export'));

        expect($response->headers->get('content-disposition'))->toContain('attachment');
        expect($response->headers->get('content-disposition'))->toContain('.csv');
    });

    it('orders export puede descargarse', function () {
        $response = actingAs($this->admin)->get(route('admin.orders.export'));

        expect($response->headers->get('content-disposition'))->toContain('attachment');
        expect($response->headers->get('content-disposition'))->toContain('.csv');
    });

    it('places export puede descargarse', function () {
        $response = actingAs($this->admin)->get(route('admin.places.export'));

        $contentDisposition = $response->headers->get('content-disposition');
        expect($contentDisposition)->not->toBeNull();
        expect($contentDisposition)->toContain('attachment');
        expect($contentDisposition)->toContain('.csv');
    });

    it('reviews export puede descargarse', function () {
        $response = actingAs($this->admin)->get(route('admin.reviews.export'));

        expect($response->headers->get('content-disposition'))->toContain('attachment');
        expect($response->headers->get('content-disposition'))->toContain('.csv');
    });

    it('usuarios no-admin no pueden descargar exports', function () {
        actingAs($this->user);

        get(route('admin.users.export'))->assertStatus(403);
        get(route('admin.pets.export'))->assertStatus(403);
        get(route('admin.products.export'))->assertStatus(403);
        get(route('admin.orders.export'))->assertStatus(403);
        get(route('admin.places.export'))->assertStatus(403);
        get(route('admin.reviews.export'))->assertStatus(403);
    });

    it('UsersExport contiene datos correctos', function () {
        $export = new UsersExport();
        $collection = $export->collection();

        expect($collection->count())->toBeGreaterThan(0);
        expect($collection->first())->toBeInstanceOf(User::class);
    });

    it('PetsExport contiene datos correctos', function () {
        $export = new PetsExport();
        $collection = $export->collection();

        expect($collection->count())->toBeGreaterThan(0);
        expect($collection->first())->toBeInstanceOf(Pet::class);
    });

    it('PlacesExport calcula calificación promedio', function () {
        $export = new PlacesExport();
        $headings = $export->headings();

        expect($headings)->toContain('Calificación promedio');
    });

    it('ReviewsExport incluye estado y motivo de rechazo', function () {
        $export = new ReviewsExport();
        $headings = $export->headings();

        expect($headings)->toContain('Estado');
        expect($headings)->toContain('Motivo del rechazo');
    });
});
