<?php

namespace App\Http\Resources;

use App\DTOs\MunicipalityDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MunicipalityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var MunicipalityDTO $municipality */
        $municipality = $this->resource;

        return [
            'name' => $municipality->name,
            'ibge_code' => $municipality->ibgeCode,
        ];
    }
}
