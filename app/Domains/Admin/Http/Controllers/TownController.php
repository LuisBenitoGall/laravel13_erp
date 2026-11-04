<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateTown;
use App\Domains\Admin\Actions\DeleteTown;
use App\Domains\Admin\Actions\ToggleTownStatus;
use App\Domains\Admin\Actions\UpdateTown;
use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Http\Requests\StoreTownRequest;
use App\Domains\Admin\Http\Requests\ToggleTownStatusRequest;
use App\Domains\Admin\Http\Requests\TownTableRequest;
use App\Domains\Admin\Http\Requests\UpdateTownRequest;
use App\Domains\Admin\Http\Resources\CountryResource;
use App\Domains\Admin\Http\Resources\ProvinceResource;
use App\Domains\Admin\Http\Resources\TownResource;
use App\Domains\Admin\Models\Country;
use App\Domains\Admin\Models\Province;
use App\Domains\Admin\Models\Town;
use App\Http\Controllers\Controller;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class TownController extends Controller
{
    public function index(Province $province): InertiaResponse
    {
        $this->authorize('viewAny', Town::class);

        $country = Country::query()->findOrFail($province->country_id);

        return Inertia::render('Admin/Towns/Index', [
            'country' => CountryResource::make($country)->resolve(),
            'province' => ProvinceResource::make($province)->resolve(),
        ]);
    }

    public function table(TownTableRequest $request, Province $province, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', Town::class);

        $query = Town::query()->where('province_id', $province->id);

        if ($request->sorts() === []) {
            $query->orderBy('name');
        }

        return $table->for($query)
            ->searchable(['name'])
            ->request($request)
            ->through(TownResource::class)
            ->json();
    }

    /** Cascada JSON (D5): poblaciones Active de la provincia, `{id, name}` por nombre. */
    public function options(Province $province): JsonResponse
    {
        $this->authorize('viewAny', Country::class);

        return response()->json(
            Town::query()
                ->where('province_id', $province->id)
                ->where('status', GeographyStatus::Active->value)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Town $town): array => [
                    'id' => $town->id,
                    'name' => $town->name,
                ])
                ->values()
                ->all(),
        );
    }

    public function store(StoreTownRequest $request, Province $province, CreateTown $action): RedirectResponse
    {
        $action->handle($province, $request->validated());

        return back()->with('success', __('poblacion_creada'));
    }

    public function update(UpdateTownRequest $request, Town $town, UpdateTown $action): RedirectResponse
    {
        $action->handle($town, $request->validated());

        return back()->with('success', __('poblacion_actualizada'));
    }

    /** Toggle activo/inactivo desde Acciones del ServerTable (JSON, sin recarga). */
    public function updateStatus(
        ToggleTownStatusRequest $request,
        Town $town,
        ToggleTownStatus $action,
    ): JsonResponse {
        $status = $request->validated('status');

        $town = $action->handle(
            $town,
            is_string($status) ? GeographyStatus::from($status) : null,
        );

        return response()->json([
            'status' => $town->status->value,
        ]);
    }

    public function destroy(Town $town, DeleteTown $action): RedirectResponse
    {
        $this->authorize('delete', $town);
        $action->handle($town);

        return to_route('admin.provinces.towns.index', $town->province_id)
            ->with('success', __('poblacion_eliminada'));
    }
}
