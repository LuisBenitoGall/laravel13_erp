<?php

namespace App\Domains\HR\Http\Controllers;

use App\Domains\HR\Actions\CreateContractType;
use App\Domains\HR\Actions\DeleteContractType;
use App\Domains\HR\Actions\UpdateContractType;
use App\Domains\HR\Enums\ContractTypeStatus;
use App\Domains\HR\Http\Requests\ContractTypeTableRequest;
use App\Domains\HR\Http\Requests\StoreContractTypeRequest;
use App\Domains\HR\Http\Requests\UpdateContractTypeRequest;
use App\Domains\HR\Http\Resources\ContractTypeResource;
use App\Domains\HR\Models\ContractType;
use App\Http\Controllers\Controller;
use App\Support\Table\ActiveFirstOrder;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ContractTypeController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', ContractType::class);

        return Inertia::render('HR/ContractTypes/Index');
    }

    public function table(ContractTypeTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', ContractType::class);

        $query = ContractType::query();

        if ($request->sorts() === []) {
            ActiveFirstOrder::apply($query, ContractTypeStatus::Active, 'name');
        }

        return $table->for($query)
            ->searchable(['name', 'slug'])
            ->request($request)
            ->through(ContractTypeResource::class)
            ->json();
    }

    public function create(): InertiaResponse
    {
        $this->authorize('create', ContractType::class);

        return Inertia::render('HR/ContractTypes/Create');
    }

    public function store(StoreContractTypeRequest $request, CreateContractType $action): RedirectResponse
    {
        $contractType = $action->handle($request->validated());

        return to_route('rrhh.contract-types.edit', $contractType)
            ->with('success', __('tipo_contrato_creado'));
    }

    public function edit(ContractType $contractType): InertiaResponse
    {
        $this->authorize('update', $contractType);

        return Inertia::render('HR/ContractTypes/Edit', [
            'contract_type' => ContractTypeResource::make($contractType)->resolve(),
        ]);
    }

    public function update(
        UpdateContractTypeRequest $request,
        ContractType $contractType,
        UpdateContractType $action,
    ): RedirectResponse {
        $action->handle($contractType, $request->validated());

        return back()->with('success', __('tipo_contrato_actualizado'));
    }

    public function destroy(ContractType $contractType, DeleteContractType $action): RedirectResponse
    {
        $this->authorize('delete', $contractType);
        $action->handle($contractType);

        return to_route('rrhh.contract-types.index')
            ->with('success', __('tipo_contrato_eliminado'));
    }
}
