<?php

use App\DTOs\MunicipalityDTO;
use App\Exceptions\ProviderException;
use App\Providers\Municipality\IbgeProvider;
use Illuminate\Support\Facades\Http;

test('it fetches municipalities from IBGE successfully', function () {
    Http::fake([
        'servicodados.ibge.gov.br/api/v1/localidades/estados/rs/municipios' => Http::response([
            ['nome' => 'Porto Alegre', 'id' => 4314902],
            ['nome' => 'Caxias do Sul', 'id' => 4305108],
        ], 200),
    ]);

    $provider = new IbgeProvider;
    $municipalities = $provider->getMunicipalities('RS');

    expect($municipalities)->toBeArray()
        ->toHaveCount(2)
        ->and($municipalities[0])->toBeInstanceOf(MunicipalityDTO::class)
        ->and($municipalities[0]->name)->toBe('Porto Alegre')
        ->and($municipalities[0]->ibgeCode)->toBe('4314902')
        ->and($municipalities[1]->name)->toBe('Caxias do Sul')
        ->and($municipalities[1]->ibgeCode)->toBe('4305108');
});

test('it throws ProviderException when IBGE fails', function () {
    Http::fake([
        'servicodados.ibge.gov.br/*' => Http::response([], 404),
    ]);

    $provider = new IbgeProvider;
    $provider->getMunicipalities('RS');
})->throws(ProviderException::class);

test('it converts UF to lowercase for IBGE API', function () {
    Http::fake([
        'servicodados.ibge.gov.br/api/v1/localidades/estados/rs/municipios' => Http::response([
            ['nome' => 'Porto Alegre', 'id' => 4314902],
        ], 200),
    ]);

    $provider = new IbgeProvider;
    $municipalities = $provider->getMunicipalities('RS');

    expect($municipalities)->toHaveCount(1);
});
