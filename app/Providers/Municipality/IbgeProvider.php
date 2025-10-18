<?php

namespace App\Providers\Municipality;

use App\DTOs\MunicipalityDTO;
use App\Exceptions\ProviderException;
use Illuminate\Support\Facades\Http;

class IbgeProvider implements MunicipalityProviderInterface
{
    private const BASE_URL = 'https://servicodados.ibge.gov.br/api/v1/localidades/estados';
    private const TIMEOUT = 10;

    public function getMunicipalities(string $uf): array
    {
        try {
            $response = Http::timeout(self::TIMEOUT)
                ->retry(3, 100)
                ->get(self::BASE_URL . '/' . strtolower($uf) . '/municipios');

            if ($response->failed()) {
                throw new ProviderException(
                    'IBGE',
                    'HTTP ' . $response->status(),
                    $response->status()
                );
            }

            return $this->mapToDTO($response->json());
        } catch (\Exception $e) {
            if ($e instanceof ProviderException) {
                throw $e;
            }

            throw new ProviderException('IBGE', $e->getMessage());
        }
    }

    private function mapToDTO(array $data): array
    {
        return array_map(
            fn (array $municipality) => new MunicipalityDTO(
                name: $municipality['nome'],
                ibgeCode: (string) $municipality['id'],
            ),
            $data
        );
    }
}
