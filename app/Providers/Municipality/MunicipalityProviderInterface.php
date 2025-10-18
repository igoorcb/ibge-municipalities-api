<?php

namespace App\Providers\Municipality;

use App\DTOs\MunicipalityDTO;

interface MunicipalityProviderInterface
{
    /**
     * @return MunicipalityDTO[]
     */
    public function getMunicipalities(string $uf): array;
}
