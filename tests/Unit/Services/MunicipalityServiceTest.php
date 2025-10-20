<?php

use App\DTOs\MunicipalityDTO;
use App\Exceptions\InvalidUfException;
use App\Services\MunicipalityService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
    config(['services.municipality.provider' => 'brasilapi']);
    config(['services.municipality.cache_ttl' => 3600]);
});

test('it validates UF correctly', function () {
    $service = new MunicipalityService;
    $service->getMunicipalitiesByUf('XX');
})->throws(InvalidUfException::class, 'UF inválida: XX');

test('it fetches municipalities and caches the result', function () {
    Http::fake([
        'brasilapi.com.br/*' => Http::response([
            ['nome' => 'Porto Alegre', 'codigo_ibge' => '4314902'],
        ], 200),
    ]);

    $service = new MunicipalityService;

    $municipalities = $service->getMunicipalitiesByUf('RS');

    expect($municipalities)->toBeArray()
        ->toHaveCount(1)
        ->and($municipalities[0])->toBeInstanceOf(MunicipalityDTO::class);

    expect(Cache::has('municipalities:brasilapi:RS'))->toBeTrue();
});

test('it returns cached data on subsequent calls', function () {
    Http::fake([
        'brasilapi.com.br/*' => Http::response([
            ['nome' => 'Porto Alegre', 'codigo_ibge' => '4314902'],
        ], 200),
    ]);

    $service = new MunicipalityService;

    $municipalities1 = $service->getMunicipalitiesByUf('RS');

    Http::fake();

    $municipalities2 = $service->getMunicipalitiesByUf('RS');

    expect($municipalities1)->toEqual($municipalities2);

    Http::assertNothingSent();
});

test('it converts UF to uppercase', function () {
    Http::fake([
        'brasilapi.com.br/*' => Http::response([
            ['nome' => 'Porto Alegre', 'codigo_ibge' => '4314902'],
        ], 200),
    ]);

    $service = new MunicipalityService;
    $municipalities = $service->getMunicipalitiesByUf('rs');

    expect($municipalities)->toHaveCount(1);
    expect(Cache::has('municipalities:brasilapi:RS'))->toBeTrue();
});
