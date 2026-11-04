<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateVatType;
use App\Domains\Admin\Actions\DeleteVatType;
use App\Domains\Admin\Actions\ToggleVatTypeStatus;
use App\Domains\Admin\Actions\UpdateVatType;
use App\Domains\Admin\Enums\VatTypeStatus;
use App\Domains\Admin\Http\Requests\StoreVatTypeRequest;
use App\Domains\Admin\Http\Requests\ToggleVatTypeStatusRequest;
use App\Domains\Admin\Http\Requests\UpdateVatTypeRequest;
use App\Domains\Admin\Http\Requests\VatTypeTableRequest;
use App\Domains\Admin\Http\Resources\VatTypeResource;
use App\Domains\Admin\Models\Country;
use App\Domains\Admin\Models\VatType;
use App\Http\Controllers\Controller;
use App\Support\Table\ActiveFirstOrder;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class VatTypeController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', VatType::class);

        $countries = Country::query()
            ->orderBy('name')
            ->get(['id', 'name', 'status'])
            ->map(fn (Country $country): array => [
                'id' => $country->id,
                'name' => $country->name,
                'status' => $country->status->value,
            ])
            ->values()
            ->all();

        return Inertia::render('Admin/VatTypes/Index', [
            'countries' => $countries,
        ]);
    }

    public function table(VatTypeTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', VatType::class);

        $query = VatType::query()->with('country');

        if ($request->sorts() === []) {
            ActiveFirstOrder::apply($query, VatTypeStatus::Active, 'name');
        }

        return $table->for($query)
            ->searchable(['name'])
            ->request($request)
            ->through(VatTypeResource::class)
            ->json();
    }

    public function store(StoreVatTypeRequest $request, CreateVatType $action): RedirectResponse
    {
        $action->handle($request->validated());

        return back()->with('success', __('tipo_iva_creado'));
    }

    public function update(UpdateVatTypeRequest $request, VatType $vatType, UpdateVatType $action): RedirectResponse
    {
        $action->handle($vatType, $request->validated());

        return back()->with('success', __('tipo_iva_actualizado'));
    }

    public function updateStatus(
        ToggleVatTypeStatusRequest $request,
        VatType $vatType,
        ToggleVatTypeStatus $action,
    ): JsonResponse {
        $status = $request->validated('status');

        $vatType = $action->handle(
            $vatType,
            is_string($status) ? VatTypeStatus::from($status) : null,
        );

        return response()->json([
            'status' => $vatType->status->value,
        ]);
    }

    public function destroy(VatType $vatType, DeleteVatType $action): RedirectResponse
    {
        $this->authorize('delete', $vatType);
        $action->handle($vatType);

        return to_route('admin.vat-types.index')
            ->with('success', __('tipo_iva_eliminado'));
    }
}
