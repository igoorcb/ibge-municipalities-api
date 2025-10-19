<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MunicipalityCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => MunicipalityResource::collection($this->collection),
        ];
    }

    public function paginationInformation($request, $paginated, $default): array
    {
        return [
            'meta' => [
                'current_page' => $default['meta']['current_page'],
                'per_page' => $default['meta']['per_page'],
                'total' => $default['meta']['total'],
                'last_page' => $default['meta']['last_page'],
            ],
            'links' => $default['links'],
        ];
    }
}
