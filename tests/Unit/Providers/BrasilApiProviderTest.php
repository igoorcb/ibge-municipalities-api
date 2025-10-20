<?php

use App\DTOs\MunicipalityDTO;
use App\Exceptions\ProviderException;
use App\Providers\Municipality\BrasilApiProvider;
use Illuminate\Support\Facades\Http;

test('it fetches municipalities from BrasilAPI successfully', function () {
    Http::fake([
        'brasilapi.com.br/api/ibge/municipios/v1/RS' => Http::response([
            ['nome' => 'Porto Alegre', 'codigo_ibge' => '4314902'],
            ['nome' => 'Caxias do Sul', 'codigo_ibge' => '4305108'],
        ], 200),
    ]);

    $provider = new BrasilApiProvider();
    $municipalities = $provider->getMunicipalities('RS');

    expect($municipalities)->toBeArray()
        ->toHaveCount(2)
        ->and($municipalities[0])->toBeInstanceOf(MunicipalityDTO::class)
        ->and($municipalities[0]->name)->toBe('Porto Alegre')
        ->and($municipalities[0]->ibgeCode)->toBe('4314902')
        ->and($municipalities[1]->name)->toBe('Caxias do Sul')
        ->and($municipalities[1]->ibgeCode)->toBe('4305108');
});

test('it throws ProviderException when BrasilAPI fails', function () {
    Http::fake([
        'brasilapi.com.br/*' => Http::response([], 500),
    ]);

    $provider = new BrasilApiProvider();
    $provider->getMunicipalities('RS');
})->throws(ProviderException::class);

test('it converts UF to uppercase', function () {
    Http::fake([
        'brasilapi.com.br/api/ibge/municipios/v1/RS' => Http::response([
            ['nome' => 'Porto Alegre', 'codigo_ibge' => '4314902'],
        ], 200),
    ]);

    $provider = new BrasilApiProvider();
    $municipalities = $provider->getMunicipalities('rs');

    expect($municipalities)->toHaveCount(1);
});
