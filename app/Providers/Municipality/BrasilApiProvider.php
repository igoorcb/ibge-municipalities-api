<?php

namespace App\Providers\Municipality;

use App\DTOs\MunicipalityDTO;
use App\Exceptions\ProviderException;
use Illuminate\Support\Facades\Http;

class BrasilApiProvider implements MunicipalityProviderInterface
{
    private const BASE_URL = 'https://brasilapi.com.br/api/ibge/municipios/v1';
    private const TIMEOUT = 10;

    public function getMunicipalities(string $uf): array
    {
        try {
            $response = Http::timeout(self::TIMEOUT)
                ->retry(3, 100)
                ->get(self::BASE_URL . '/' . strtoupper($uf));

            if ($response->failed()) {
                throw new ProviderException(
                    'BrasilAPI',
                    'HTTP ' . $response->status(),
                    $response->status()
                );
            }

            return $this->mapToDTO($response->json());
        } catch (\Exception $e) {
            if ($e instanceof ProviderException) {
                throw $e;
            }

            throw new ProviderException('BrasilAPI', $e->getMessage());
        }
    }

    private function mapToDTO(array $data): array
    {
        return array_map(
            fn (array $municipality) => new MunicipalityDTO(
                name: $municipality['nome'],
                ibgeCode: (string) $municipality['codigo_ibge'],
            ),
            $data
        );
    }
}
