<?php

namespace App\Domains\Calendar\Http\Controllers;

use App\Domains\Calendar\Actions\CreateDayType;
use App\Domains\Calendar\Actions\DeleteDayType;
use App\Domains\Calendar\Actions\UpdateDayType;
use App\Domains\Calendar\Http\Requests\DayTypeTableRequest;
use App\Domains\Calendar\Http\Requests\StoreDayTypeRequest;
use App\Domains\Calendar\Http\Requests\UpdateDayTypeRequest;
use App\Domains\Calendar\Http\Resources\DayTypeResource;
use App\Domains\Calendar\Models\DayType;
use App\Http\Controllers\Controller;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DayTypeController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', DayType::class);

        return Inertia::render('HR/DayTypes/Index');
    }

    public function table(DayTypeTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', DayType::class);

        $query = DayType::query();

        if ($request->sorts() === []) {
            $query->orderBy('name');
        }

        return $table->for($query)
            ->searchable(['name', 'color'])
            ->request($request)
            ->through(DayTypeResource::class)
            ->json();
    }

    public function create(): InertiaResponse
    {
        $this->authorize('create', DayType::class);

        return Inertia::render('HR/DayTypes/Create');
    }

    public function store(StoreDayTypeRequest $request, CreateDayType $action): RedirectResponse
    {
        $dayType = $action->handle($request->validated());

        return to_route('rrhh.day-types.edit', $dayType)
            ->with('success', __('tipo_jornada_creado'));
    }

    public function edit(DayType $dayType): InertiaResponse
    {
        $this->authorize('update', $dayType);

        return Inertia::render('HR/DayTypes/Edit', [
            'day_type' => DayTypeResource::make($dayType)->resolve(),
        ]);
    }

    public function update(
        UpdateDayTypeRequest $request,
        DayType $dayType,
        UpdateDayType $action,
    ): RedirectResponse {
        $action->handle($dayType, $request->validated());

        return back()->with('success', __('tipo_jornada_actualizado'));
    }

    public function destroy(DayType $dayType, DeleteDayType $action): RedirectResponse
    {
        $this->authorize('delete', $dayType);
        $action->handle($dayType);

        return to_route('rrhh.day-types.index')
            ->with('success', __('tipo_jornada_eliminado'));
    }
}
