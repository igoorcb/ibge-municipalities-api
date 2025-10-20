<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListMunicipalitiesRequest;
use App\Http\Resources\MunicipalityCollection;
use App\Services\MunicipalityService;
use Illuminate\Pagination\LengthAwarePaginator;

class MunicipalityController extends Controller
{
    public function __construct(
        private readonly MunicipalityService $municipalityService
    ) {
    }

    public function index(string $uf, ListMunicipalitiesRequest $request)
    {
        $municipalities = $this->municipalityService->getMunicipalitiesByUf($uf);

        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

        $paginated = new LengthAwarePaginator(
            items: array_slice($municipalities, ($page - 1) * $perPage, $perPage),
            total: count($municipalities),
            perPage: $perPage,
            currentPage: $page,
            options: ['path' => $request->url(), 'query' => $request->query()]
        );

        return new MunicipalityCollection($paginated);
    }
}
