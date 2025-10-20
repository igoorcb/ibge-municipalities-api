<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
    config(['services.municipality.provider' => 'brasilapi']);
});

test('it returns municipalities for valid UF with pagination', function () {
    Http::fake([
        'brasilapi.com.br/*' => Http::response([
            ['nome' => 'Porto Alegre', 'codigo_ibge' => '4314902'],
            ['nome' => 'Caxias do Sul', 'codigo_ibge' => '4305108'],
            ['nome' => 'Pelotas', 'codigo_ibge' => '4314407'],
        ], 200),
    ]);

    $response = $this->getJson('/api/municipalities/RS?per_page=2');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['name', 'ibge_code'],
            ],
            'meta' => [
                'current_page',
                'per_page',
                'total',
                'last_page',
            ],
            'links',
        ])
        ->assertJsonCount(2, 'data')
        ->assertJson([
            'meta' => [
                'current_page' => 1,
                'per_page' => 2,
                'total' => 3,
                'last_page' => 2,
            ],
        ]);
});

test('it returns 422 for invalid UF', function () {
    $response = $this->getJson('/api/municipalities/XX');

    $response->assertStatus(422)
        ->assertJson([
            'message' => 'UF inválida: XX. Por favor, informe uma sigla de estado válida.',
        ]);
});

test('it validates per_page parameter', function () {
    Http::fake([
        'brasilapi.com.br/*' => Http::response([
            ['nome' => 'Porto Alegre', 'codigo_ibge' => '4314902'],
        ], 200),
    ]);

    $response = $this->getJson('/api/municipalities/RS?per_page=invalid');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['per_page']);
});

test('it accepts lowercase UF', function () {
    Http::fake([
        'brasilapi.com.br/*' => Http::response([
            ['nome' => 'Porto Alegre', 'codigo_ibge' => '4314902'],
        ], 200),
    ]);

    $response = $this->getJson('/api/municipalities/rs');

    $response->assertStatus(200);
});

test('it returns second page correctly', function () {
    Http::fake([
        'brasilapi.com.br/*' => Http::response([
            ['nome' => 'Municipality 1', 'codigo_ibge' => '1'],
            ['nome' => 'Municipality 2', 'codigo_ibge' => '2'],
            ['nome' => 'Municipality 3', 'codigo_ibge' => '3'],
            ['nome' => 'Municipality 4', 'codigo_ibge' => '4'],
            ['nome' => 'Municipality 5', 'codigo_ibge' => '5'],
        ], 200),
    ]);

    $response = $this->getJson('/api/municipalities/RS?per_page=2&page=2');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJson([
            'data' => [
                ['name' => 'Municipality 3', 'ibge_code' => '3'],
                ['name' => 'Municipality 4', 'ibge_code' => '4'],
            ],
            'meta' => [
                'current_page' => 2,
                'per_page' => 2,
                'total' => 5,
            ],
        ]);
});

test('it uses cache on subsequent requests', function () {
    Http::fake([
        'brasilapi.com.br/*' => Http::response([
            ['nome' => 'Porto Alegre', 'codigo_ibge' => '4314902'],
        ], 200),
    ]);

    $this->getJson('/api/municipalities/RS');

    Http::fake();

    $response = $this->getJson('/api/municipalities/RS');

    $response->assertStatus(200);
    Http::assertNothingSent();
});
