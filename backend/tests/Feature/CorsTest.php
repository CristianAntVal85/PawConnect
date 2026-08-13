<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('preflight CORS permite origen configurado', function () {
    config()->set('cors.allowed_origins', ['http://localhost:3000']);

    $response = $this->withHeaders([
        'Origin' => 'http://localhost:3000',
        'Access-Control-Request-Method' => 'GET',
    ])->options('/api/v1/products');

    $response->assertNoContent();
    $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
});

test('CORS bloquea origen no permitido', function () {
    config()->set('cors.allowed_origins', ['http://localhost:3000']);

    $response = $this->withHeaders([
        'Origin' => 'https://evil.example',
        'Access-Control-Request-Method' => 'GET',
    ])->options('/api/v1/products');

    $allowOrigin = $response->headers->get('Access-Control-Allow-Origin');

    // El origen malicioso no debe ser reflejado por el servidor.
    expect($allowOrigin)->not->toBe('https://evil.example');
});
