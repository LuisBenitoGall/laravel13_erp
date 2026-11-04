<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateProvince;
use App\Domains\Admin\Actions\DeleteProvince;
use App\Domains\Admin\Actions\ToggleProvinceStatus;
use App\Domains\Admin\Actions\UpdateProvince;
use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Http\Requests\ProvinceTableRequest;
use App\Domains\Admin\Http\Requests\StoreProvinceRequest;
use App\Domains\Admin\Http\Requests\ToggleProvinceStatusRequest;
use App\Domains\Admin\Http\Requests\UpdateProvinceRequest;
use App\Domains\Admin\Http\Resources\CountryResource;
use App\Domains\Admin\Http\Resources\ProvinceResource;
use App\Domains\Admin\Models\Country;
use App\Domains\Admin\Models\Province;
use App\Http\Controllers\Controller;
use App\Support\Table\ActiveFirstOrder;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ProvinceController extends Controller
{
    public function index(Country $country): InertiaResponse
    {
        $this->authorize('viewAny', Province::class);

        return Inertia::render('Admin/Provinces/Index', [
            'country' => CountryResource::make($country)->resolve(),
        ]);
    }

    public function table(ProvinceTableRequest $request, Country $country, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', Province::class);

        $query = Province::query()
            ->where('country_id', $country->id)
            ->withCount('towns');

        if ($request->sorts() === []) {
            ActiveFirstOrder::apply($query, GeographyStatus::Active, 'name');
        }

        return $table->for($query)
            ->searchable(['name'])
            ->request($request)
            ->through(ProvinceResource::class)
            ->json();
    }

    /** Cascada JSON (D5): provincias Active del país, `{id, name}` por nombre. */
    public function options(Country $country): JsonResponse
    {
        $this->authorize('viewAny', Country::class);

        return response()->json(
            Province::query()
                ->where('country_id', $country->id)
                ->where('status', GeographyStatus::Active->value)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Province $province): array => [
                    'id' => $province->id,
                    'name' => $province->name,
                ])
                ->values()
                ->all(),
        );
    }

    public function store(StoreProvinceRequest $request, Country $country, CreateProvince $action): RedirectResponse
    {
        $action->handle($country, $request->validated());

        return back()->with('success', __('provincia_creada'));
    }

    public function update(UpdateProvinceRequest $request, Province $province, UpdateProvince $action): RedirectResponse
    {
        $action->handle($province, $request->validated());

        return back()->with('success', __('provincia_actualizada'));
    }

    /** Toggle activo/inactivo desde Acciones del ServerTable (JSON, sin recarga). */
    public function updateStatus(
        ToggleProvinceStatusRequest $request,
        Province $province,
        ToggleProvinceStatus $action,
    ): JsonResponse {
        $status = $request->validated('status');

        $province = $action->handle(
            $province,
            is_string($status) ? GeographyStatus::from($status) : null,
        );

        return response()->json([
            'status' => $province->status->value,
        ]);
    }

    public function destroy(Province $province, DeleteProvince $action): RedirectResponse
    {
        $this->authorize('delete', $province);
        $action->handle($province);

        return to_route('admin.countries.provinces.index', $province->country_id)
            ->with('success', __('provincia_eliminada'));
    }
}
