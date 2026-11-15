<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Enums\SectorStatus;
use App\Domains\Admin\Http\Requests\CompanyDirectoryIndexRequest;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Sector;
use App\Domains\Admin\Support\CompanyDirectoryQuery;
use App\Http\Controllers\Controller;
use App\Support\I18n\LocaleResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CompanyDirectoryController extends Controller
{
    public function index(
        CompanyDirectoryIndexRequest $request,
        CompanyDirectoryQuery $directory,
        LocaleResolver $locales,
    ): Response {
        $this->authorize('viewAnyCompanyDirectory');

        /** @var list<int> $sectorIds */
        $sectorIds = $request->validated('sector_ids') ?? [];
        /** @var string $sort */
        $sort = $request->validated('sort') ?? 'name_asc';

        $query = $directory->applySort(
            $directory->base($sectorIds)->with(['sectors']),
            $sort,
        );

        $paginator = $query->paginate(24)->withQueryString();

        $locale = $locales->resolve($request);
        $sectorsQuery = Sector::query()->where('status', SectorStatus::Active);
        if (DB::getDriverName() === 'sqlite') {
            $sectorsQuery->orderByRaw("json_extract(name, '$.{$locale}')");
        } else {
            $sectorsQuery->orderByRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(`name`, '$.{$locale}'))",
            );
        }

        $sectors = $sectorsQuery
            ->get(['id', 'name', 'slug'])
            ->map(fn (Sector $sector): array => [
                'id' => $sector->id,
                'name' => $sector->name,
            ])
            ->values()
            ->all();

        return Inertia::render('Admin/CompanyDirectory/Index', [
            'companies' => [
                'data' => collect($paginator->items())
                    ->map(fn (Company $company): array => $this->cardPayload($company))
                    ->values()
                    ->all(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
                'links' => [
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
            ],
            'sectors' => $sectors,
            'filters' => [
                'sector_ids' => $sectorIds,
                'sort' => $sort,
            ],
        ]);
    }

    public function show(Company $company, CompanyDirectoryQuery $directory): Response
    {
        $this->authorize('viewCompanyDirectory', $company);

        abort_unless(
            $directory->base()->whereKey($company->id)->exists(),
            404,
        );

        $company->load(['sectors']);

        return Inertia::render('Admin/CompanyDirectory/Show', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'tradename' => $company->tradename,
                'nif' => $company->nif,
                'logo_url' => $company->logo
                    ? Storage::disk('public')->url($company->logo)
                    : null,
                'sectors' => $company->sectors
                    ->map(fn (Sector $sector): array => [
                        'id' => $sector->id,
                        'name' => $sector->name,
                    ])
                    ->values()
                    ->all(),
            ],
        ]);
    }

    /** @return array{id: int, name: string, tradename: string|null, nif: string|null, logo_url: string|null, sectors: list<array{id: int, name: string}>} */
    private function cardPayload(Company $company): array
    {
        return [
            'id' => $company->id,
            'name' => $company->name,
            'tradename' => $company->tradename,
            'nif' => $company->nif,
            'logo_url' => $company->logo
                ? Storage::disk('public')->url($company->logo)
                : null,
            'sectors' => $company->relationLoaded('sectors')
                ? $company->sectors
                    ->map(fn (Sector $sector): array => [
                        'id' => $sector->id,
                        'name' => $sector->name,
                    ])
                    ->values()
                    ->all()
                : [],
        ];
    }
}
