<?php

namespace App\Services;

use App\DTOs\MunicipalityDTO;
use App\Exceptions\InvalidUfException;
use App\Providers\Municipality\MunicipalityProviderFactory;
use Illuminate\Support\Facades\Cache;

class MunicipalityService
{
    private const VALID_UFS = [
        'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
        'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
        'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO',
    ];

    public function __construct(
        private readonly MunicipalityProviderFactory $providerFactory = new MunicipalityProviderFactory(),
    ) {
    }

    /**
     * @return MunicipalityDTO[]
     */
    public function getMunicipalitiesByUf(string $uf): array
    {
        $uf = strtoupper($uf);

        $this->validateUf($uf);

        $cacheKey = $this->getCacheKey($uf);
        $cacheTtl = config('services.municipality.cache_ttl', 3600);

        return Cache::remember($cacheKey, $cacheTtl, function () use ($uf) {
            $provider = $this->providerFactory::make();
            return $provider->getMunicipalities($uf);
        });
    }

    private function validateUf(string $uf): void
    {
        if (!in_array($uf, self::VALID_UFS, true)) {
            throw new InvalidUfException($uf);
        }
    }

    private function getCacheKey(string $uf): string
    {
        $provider = config('services.municipality.provider', 'brasilapi');
        return "municipalities:{$provider}:{$uf}";
    }
}
