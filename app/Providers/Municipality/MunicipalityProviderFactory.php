<?php

namespace App\Providers\Municipality;

use InvalidArgumentException;

class MunicipalityProviderFactory
{
    public static function make(): MunicipalityProviderInterface
    {
        $provider = config('services.municipality.provider', 'brasilapi');

        return match ($provider) {
            'brasilapi' => new BrasilApiProvider(),
            'ibge' => new IbgeProvider(),
            default => throw new InvalidArgumentException("Provider inválido: {$provider}"),
        };
    }
}
