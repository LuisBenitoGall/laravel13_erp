<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateDelegation;
use App\Domains\Admin\Actions\DeleteCompany;
use App\Domains\Admin\Actions\UpdateCompany;
use App\Domains\Admin\Actions\UpdateCompanyStatus;
use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Http\Requests\DelegationTableRequest;
use App\Domains\Admin\Http\Requests\StoreDelegationRequest;
use App\Domains\Admin\Http\Requests\UpdateDelegationRequest;
use App\Domains\Admin\Http\Requests\UpdateDelegationStatusRequest;
use App\Domains\Admin\Http\Resources\CompanyResource;
use App\Domains\Admin\Http\Resources\WorkplaceResource;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Country;
use App\Domains\Admin\Models\Workplace;
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
        $this->perimeter->matrix();
        $this->authorize('createDelegation');

        return Inertia::render('Admin/Delegations/Create', [
            'countries' => $this->activeCountries(),
        ]);
    }

    public function store(StoreDelegationRequest $request, CreateDelegation $action): RedirectResponse
    {
        $matrix = $this->perimeter->matrix();
        $validated = $request->validated();

        /** @var array<string, mixed> $workplace */
        $workplace = $validated['workplace'];
        unset($validated['workplace']);

        $delegation = $action->handle($matrix, $validated, $workplace, $request->user());

        return to_route('admin.delegations.edit', $delegation)
            ->with('success', __('delegacion_creada'));
    }

    public function edit(Company $delegation): Response
    {
        $matrix = $this->perimeter->matrix();
        $this->perimeter->assertOwns($delegation);
        $this->authorize('updateDelegation', $delegation);

        $workplaces = Workplace::query()
            ->withoutGlobalScope('company')
            ->where('company_id', $delegation->id)
            ->with(['town.province.country'])
            ->orderByDesc('featured')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Delegations/Edit', [
            'delegation' => CompanyResource::make($delegation)->resolve(),
            'matrix' => ['id' => $matrix->id, 'name' => $matrix->name],
            'workplaces' => WorkplaceResource::collection($workplaces)->resolve(),
            'countries' => $this->activeCountries(),
        ]);
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
