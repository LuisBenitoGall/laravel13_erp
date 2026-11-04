<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateCountry;
use App\Domains\Admin\Actions\DeleteCountry;
use App\Domains\Admin\Actions\ToggleCountryStatus;
use App\Domains\Admin\Actions\UpdateCountry;
use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Http\Requests\CountryTableRequest;
use App\Domains\Admin\Http\Requests\StoreCountryRequest;
use App\Domains\Admin\Http\Requests\ToggleCountryStatusRequest;
use App\Domains\Admin\Http\Requests\UpdateCountryRequest;
use App\Domains\Admin\Http\Resources\CountryResource;
use App\Domains\Admin\Models\Country;
use App\Http\Controllers\Controller;
use App\Support\Table\ActiveFirstOrder;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CountryController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Country::class);

        return Inertia::render('Admin/Countries/Index');
    }

    public function table(CountryTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', Country::class);

        $query = Country::query()->withCount('provinces');

        if ($request->sorts() === []) {
            ActiveFirstOrder::apply($query, GeographyStatus::Active, 'name');
        }

        return $table->for($query)
            ->searchable(['name', 'code', 'alfa2', 'alfa3'])
            ->request($request)
            ->through(CountryResource::class)
            ->json();
    }

    public function store(StoreCountryRequest $request, CreateCountry $action): RedirectResponse
    {
        $action->handle($request->validated());

        return back()->with('success', __('pais_creado'));
    }

    public function update(UpdateCountryRequest $request, Country $country, UpdateCountry $action): RedirectResponse
    {
        $action->handle($country, $request->validated());

        return back()->with('success', __('pais_actualizado'));
    }

    /** Toggle activo/inactivo desde Acciones del ServerTable (JSON, sin recarga). */
    public function updateStatus(
        ToggleCountryStatusRequest $request,
        Country $country,
        ToggleCountryStatus $action,
    ): JsonResponse {
        $status = $request->validated('status');

        $country = $action->handle(
            $country,
            is_string($status) ? GeographyStatus::from($status) : null,
        );

        return response()->json([
            'status' => $country->status->value,
        ]);
    }

    public function destroy(Country $country, DeleteCountry $action): RedirectResponse
    {
        $this->authorize('delete', $country);
        $action->handle($country);

        return to_route('admin.countries.index')
            ->with('success', __('pais_eliminado'));
    }
}
