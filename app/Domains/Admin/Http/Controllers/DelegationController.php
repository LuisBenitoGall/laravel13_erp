<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateDelegation;
use App\Domains\Admin\Actions\DeleteCompany;
use App\Domains\Admin\Actions\SetDelegationPreferredWorkplace;
use App\Domains\Admin\Actions\SyncCompanyCurrencies;
use App\Domains\Admin\Actions\UpdateCompany;
use App\Domains\Admin\Actions\UpdateCompanyStatus;
use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Http\Requests\DelegationTableRequest;
use App\Domains\Admin\Http\Requests\DelegationWorkplaceTableRequest;
use App\Domains\Admin\Http\Requests\SetDelegationPreferredWorkplaceRequest;
use App\Domains\Admin\Http\Requests\StoreDelegationRequest;
use App\Domains\Admin\Http\Requests\SyncDelegationCurrenciesRequest;
use App\Domains\Admin\Http\Requests\UpdateDelegationRequest;
use App\Domains\Admin\Http\Requests\UpdateDelegationStatusRequest;
use App\Domains\Admin\Http\Resources\CompanyResource;
use App\Domains\Admin\Http\Resources\DelegationLinkedWorkplaceResource;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Country;
use App\Domains\Admin\Models\Workplace;
use App\Domains\Admin\Support\CompanyCurrencyInertiaData;
use App\Domains\Admin\Support\DelegationPerimeter;
use App\Http\Controllers\Controller;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD de Delegaciones: Companies hijas de la matriz de sesión (≠ Mis empresas).
 * La sesión sigue siendo la matriz; la hija se gestiona con company_id explícito.
 */
class DelegationController extends Controller
{
    public function __construct(
        private readonly DelegationPerimeter $perimeter,
    ) {}

    public function index(): Response
    {
        $this->perimeter->matrix();
        $this->authorize('viewAnyDelegation');

        return Inertia::render('Admin/Delegations/Index');
    }

    public function table(DelegationTableRequest $request, TableBuilder $table): JsonResponse
    {
        $query = $this->perimeter->query();
        $this->authorize('viewAnyDelegation');

        return $table->for($query)
            ->searchable(['name', 'nif', 'tradename'])
            ->request($request)
            ->through(CompanyResource::class)
            ->json();
    }

    public function create(): Response
    {
        $matrix = $this->perimeter->matrix();
        $this->authorize('createDelegation');

        $matrixWorkplaces = Workplace::query()
            ->withoutGlobalScope('company')
            ->where('company_id', $matrix->id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Workplace $workplace): array => [
                'id' => $workplace->id,
                'name' => $workplace->name,
            ])
            ->values()
            ->all();

        return Inertia::render('Admin/Delegations/Create', [
            'countries' => $this->activeCountries(),
            'matrix_workplaces' => $matrixWorkplaces,
        ]);
    }

    public function store(StoreDelegationRequest $request, CreateDelegation $action): RedirectResponse
    {
        $matrix = $this->perimeter->matrix();
        $validated = $request->validated();

        /** @var array<string, mixed>|null $workplace */
        $workplace = $validated['workplace'] ?? null;
        unset($validated['workplace']);

        /** @var list<int> $workplaceIds */
        $workplaceIds = array_values(array_map('intval', $validated['workplace_ids'] ?? []));
        unset($validated['workplace_ids']);

        $hasNewWorkplace = filled($workplace['name'] ?? null);
        $delegation = $action->handle(
            $matrix,
            $validated,
            $hasNewWorkplace ? $workplace : null,
            $workplaceIds,
            $request->user(),
        );

        return to_route('admin.delegations.edit', $delegation)
            ->with('success', __('delegacion_creada'));
    }

    public function edit(Company $delegation): Response
    {
        $matrix = $this->perimeter->matrix();
        $this->perimeter->assertOwns($delegation);
        $this->authorize('updateDelegation', $delegation);

        $delegation->load('currencies');

        return Inertia::render('Admin/Delegations/Edit', [
            'delegation' => CompanyResource::make($delegation)->resolve(),
            'matrix' => ['id' => $matrix->id, 'name' => $matrix->name],
            ...CompanyCurrencyInertiaData::for($delegation),
            'can' => [
                'update_company_currencies' => request()->user()?->can('updateDelegation', $delegation) ?? false,
                'update_preferred_workplace' => request()->user()?->can('updateDelegation', $delegation) ?? false,
            ],
        ]);
    }

    public function workplacesTable(
        DelegationWorkplaceTableRequest $request,
        Company $delegation,
        TableBuilder $table,
    ): JsonResponse {
        $matrix = $this->perimeter->matrix();
        $this->perimeter->assertOwns($delegation);
        $this->authorize('updateDelegation', $delegation);

        $query = Workplace::query()
            ->withoutGlobalScope('company')
            ->select([
                'workplaces.*',
                'workplace_delegation.is_preferred_seat as is_preferred_seat',
            ])
            ->join(
                'workplace_delegation',
                'workplaces.id',
                '=',
                'workplace_delegation.workplace_id',
            )
            ->where('workplace_delegation.company_id', $delegation->id)
            ->where('workplaces.company_id', $matrix->id)
            ->with(['town.province.country']);

        if ($request->sorts() === []) {
            $query->orderBy('workplaces.name');
        }

        return $table->for($query)
            ->searchable(['workplaces.name', 'workplaces.cp', 'workplaces.address'])
            ->filterUsing(
                'is_preferred_seat',
                function ($builder, string $type, mixed $value): void {
                    $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    if ($bool === null) {
                        return;
                    }

                    match ($type) {
                        '=', 'eq' => $builder->where('workplace_delegation.is_preferred_seat', $bool),
                        '!=', 'neq' => $builder->where('workplace_delegation.is_preferred_seat', '!=', $bool),
                        default => null,
                    };
                },
            )
            ->filterUsing(
                'town_name',
                function ($builder, string $type, mixed $value): void {
                    $term = '%'.(string) $value.'%';
                    match ($type) {
                        'like', '=', 'eq' => $builder->whereHas(
                            'town',
                            fn ($town) => $town->where('name', 'like', $term),
                        ),
                        default => null,
                    };
                },
            )
            ->filterUsing(
                'province_name',
                function ($builder, string $type, mixed $value): void {
                    $term = '%'.(string) $value.'%';
                    match ($type) {
                        'like', '=', 'eq' => $builder->whereHas(
                            'town.province',
                            fn ($province) => $province->where('name', 'like', $term),
                        ),
                        default => null,
                    };
                },
            )
            ->filterUsing(
                'country_name',
                function ($builder, string $type, mixed $value): void {
                    $term = '%'.(string) $value.'%';
                    match ($type) {
                        'like', '=', 'eq' => $builder->whereHas(
                            'town.province.country',
                            fn ($country) => $country->where('name', 'like', $term),
                        ),
                        default => null,
                    };
                },
            )
            ->filterUsing(
                'created_at',
                function ($builder, string $type, mixed $value): void {
                    if ($type !== 'date_between' || ! is_array($value)) {
                        return;
                    }

                    $from = is_string($value['from'] ?? null) && $value['from'] !== ''
                        ? $value['from']
                        : null;
                    $to = is_string($value['to'] ?? null) && $value['to'] !== ''
                        ? $value['to']
                        : null;

                    if ($from !== null && $to !== null && $from > $to) {
                        [$from, $to] = [$to, $from];
                    }

                    if ($from !== null) {
                        $builder->whereDate('workplaces.created_at', '>=', $from);
                    }

                    if ($to !== null) {
                        $builder->whereDate('workplaces.created_at', '<=', $to);
                    }
                },
            )
            ->request($request)
            ->through(DelegationLinkedWorkplaceResource::class)
            ->json();
    }

    public function setPreferredWorkplace(
        SetDelegationPreferredWorkplaceRequest $request,
        Company $delegation,
        SetDelegationPreferredWorkplace $action,
    ): JsonResponse {
        $this->perimeter->assertOwns($delegation);

        $workplaceId = $request->validated('workplace_id');
        $action->handle(
            $delegation,
            $workplaceId !== null ? (int) $workplaceId : null,
        );

        return response()->json([
            'workplace_id' => $workplaceId !== null ? (int) $workplaceId : null,
            'is_preferred_seat' => $workplaceId !== null,
        ]);
    }

    public function syncCurrencies(
        SyncDelegationCurrenciesRequest $request,
        Company $delegation,
        SyncCompanyCurrencies $action,
    ): RedirectResponse {
        $this->perimeter->assertOwns($delegation);

        /** @var list<int|string> $currencyIds */
        $currencyIds = $request->validated('currency_ids');
        $defaultId = $request->validated('default_currency_id');
        $action->handle(
            $delegation,
            $currencyIds,
            $defaultId !== null ? (int) $defaultId : null,
        );

        return back()->with('success', __('monedas_empresa_actualizadas'));
    }

    public function update(
        UpdateDelegationRequest $request,
        Company $delegation,
        UpdateCompany $action,
    ): RedirectResponse {
        $this->perimeter->assertOwns($delegation);
        $this->authorize('updateDelegation', $delegation);

        // Solo campos de empresa: parent_id no es editable aquí.
        $action->handle($delegation, $request->validated());

        return back()->with('success', __('delegacion_actualizada'));
    }

    public function updateStatus(
        UpdateDelegationStatusRequest $request,
        Company $delegation,
        UpdateCompanyStatus $action,
    ): JsonResponse {
        /** @var string $statusValue */
        $statusValue = $request->validated('status');
        $delegation = $action->handle($delegation, CompanyStatus::from($statusValue));

        return response()->json([
            'status' => $delegation->status->value,
        ]);
    }

    public function destroy(Company $delegation, DeleteCompany $action): RedirectResponse
    {
        $this->perimeter->assertOwns($delegation);
        $this->authorize('deleteDelegation', $delegation);

        $action->handle($delegation);

        return to_route('admin.delegations.index')
            ->with('success', __('delegacion_eliminada'));
    }

    /** @return list<array{id: int, name: string}> */
    private function activeCountries(): array
    {
        return Country::query()
            ->where('status', GeographyStatus::Active->value)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Country $country): array => [
                'id' => $country->id,
                'name' => $country->name,
            ])
            ->values()
            ->all();
    }
}
