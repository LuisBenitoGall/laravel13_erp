<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateUnit;
use App\Domains\Admin\Actions\DeleteUnit;
use App\Domains\Admin\Actions\ToggleUnitStatus;
use App\Domains\Admin\Actions\UpdateUnit;
use App\Domains\Admin\Enums\UnitStatus;
use App\Domains\Admin\Http\Requests\StoreUnitRequest;
use App\Domains\Admin\Http\Requests\ToggleUnitStatusRequest;
use App\Domains\Admin\Http\Requests\UnitTableRequest;
use App\Domains\Admin\Http\Requests\UpdateUnitRequest;
use App\Domains\Admin\Http\Resources\UnitResource;
use App\Domains\Admin\Models\Unit;
use App\Http\Controllers\Controller;
use App\Support\Table\ActiveFirstOrder;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class UnitController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Unit::class);

        return Inertia::render('Admin/Units/Index');
    }

    public function table(UnitTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', Unit::class);

        $query = Unit::query();

        if ($request->sorts() === []) {
            ActiveFirstOrder::apply($query, UnitStatus::Active, 'name');
        }

        return $table->for($query)
            ->searchable(['name', 'symbol'])
            ->request($request)
            ->through(UnitResource::class)
            ->json();
    }

    public function store(StoreUnitRequest $request, CreateUnit $action): RedirectResponse
    {
        $action->handle($request->validated());

        return back()->with('success', __('unidad_creada'));
    }

    public function update(UpdateUnitRequest $request, Unit $unit, UpdateUnit $action): RedirectResponse
    {
        $action->handle($unit, $request->validated());

        return back()->with('success', __('unidad_actualizada'));
    }

    public function updateStatus(
        ToggleUnitStatusRequest $request,
        Unit $unit,
        ToggleUnitStatus $action,
    ): JsonResponse {
        $status = $request->validated('status');

        $unit = $action->handle(
            $unit,
            is_string($status) ? UnitStatus::from($status) : null,
        );

        return response()->json([
            'status' => $unit->status->value,
        ]);
    }

    public function destroy(Unit $unit, DeleteUnit $action): RedirectResponse
    {
        $this->authorize('delete', $unit);
        $action->handle($unit);

        return to_route('admin.units.index')
            ->with('success', __('unidad_eliminada'));
    }
}
