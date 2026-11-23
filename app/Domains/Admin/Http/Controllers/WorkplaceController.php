<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateWorkplace;
use App\Domains\Admin\Actions\DeleteWorkplace;
use App\Domains\Admin\Actions\ToggleWorkplaceStatus;
use App\Domains\Admin\Actions\UpdateWorkplace;
use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Enums\WorkplaceStatus;
use App\Domains\Admin\Http\Requests\StoreWorkplaceRequest;
use App\Domains\Admin\Http\Requests\ToggleWorkplaceStatusRequest;
use App\Domains\Admin\Http\Requests\UpdateWorkplaceRequest;
use App\Domains\Admin\Http\Requests\WorkplaceTableRequest;
use App\Domains\Admin\Http\Resources\WorkplaceResource;
use App\Domains\Admin\Models\Country;
use App\Domains\Admin\Models\Province;
use App\Domains\Admin\Models\Town;
use App\Domains\Admin\Models\Workplace;
use App\Http\Controllers\Controller;
use App\Support\Table\ActiveFirstOrder;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class WorkplaceController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Workplace::class);

        return Inertia::render('Admin/Workplaces/Index');
    }

    public function table(WorkplaceTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', Workplace::class);

        $query = Workplace::query()->with(['town.province.country']);

        if ($request->sorts() === []) {
            ActiveFirstOrder::apply($query, WorkplaceStatus::Active, 'name');
        }

        return $table->for($query)
            ->searchable(['name', 'cp', 'address'])
            ->request($request)
            ->through(WorkplaceResource::class)
            ->json();
    }

    public function create(Request $request): InertiaResponse
    {
        $this->authorize('create', Workplace::class);

        // Principal ON si no hay ningún centro Active; OFF si ya existe ≥1 Active.
        $defaultFeatured = ! Workplace::query()
            ->where('status', WorkplaceStatus::Active)
            ->exists();

        $returnTo = $request->query('return_to');
        $returnTo = is_string($returnTo) && preg_match('#^/admin/company-settings(?:\?.*)?$#', $returnTo)
            ? $returnTo
            : null;

        return Inertia::render('Admin/Workplaces/Create', [
            'countries' => $this->activeCountries(),
            'default_featured' => $defaultFeatured,
            'return_to' => $returnTo,
        ]);
    }

    public function store(StoreWorkplaceRequest $request, CreateWorkplace $action): RedirectResponse
    {
        $payload = $request->safe()->except(['return_to']);
        $workplace = $action->handle($payload);

        $returnTo = $request->validated('return_to');
        if (is_string($returnTo) && $returnTo !== '') {
            return redirect()
                ->to($returnTo)
                ->with('success', __('centro_trabajo_creado'));
        }

        return to_route('admin.workplaces.edit', $workplace)
            ->with('success', __('centro_trabajo_creado'));
    }

    public function edit(Workplace $workplace): InertiaResponse
    {
        $this->authorize('update', $workplace);
        $workplace->load(['town.province.country']);

        return Inertia::render('Admin/Workplaces/Edit', [
            'workplace' => WorkplaceResource::make($workplace)->resolve(),
            'countries' => $this->activeCountries(),
        ]);
    }

    public function update(
        UpdateWorkplaceRequest $request,
        Workplace $workplace,
        UpdateWorkplace $action,
    ): RedirectResponse {
        $action->handle($workplace, $request->validated());

        return back()->with('success', __('centro_trabajo_actualizado'));
    }

    public function updateStatus(
        ToggleWorkplaceStatusRequest $request,
        Workplace $workplace,
        ToggleWorkplaceStatus $action,
    ): JsonResponse {
        $status = $request->validated('status');

        $workplace = $action->handle(
            $workplace,
            is_string($status) ? WorkplaceStatus::from($status) : null,
        );

        return response()->json([
            'status' => $workplace->status->value,
        ]);
    }

    public function destroy(Workplace $workplace, DeleteWorkplace $action): RedirectResponse
    {
        $this->authorize('delete', $workplace);
        $action->handle($workplace);

        return to_route('admin.workplaces.index')
            ->with('success', __('centro_trabajo_eliminado'));
    }

    /** Cascada geografía para actores con workplaces.* (sin exigir countries.view). */
    public function countryOptions(): JsonResponse
    {
        $this->authorizeGeographyCascade();

        return response()->json($this->activeCountries());
    }

    public function provinceOptions(Country $country): JsonResponse
    {
        $this->authorizeGeographyCascade();

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

    public function townOptions(Province $province): JsonResponse
    {
        $this->authorizeGeographyCascade();

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

    private function authorizeGeographyCascade(): void
    {
        $user = request()->user();
        abort_unless(
            $user !== null && (
                $user->can('create', Workplace::class)
                || $user->can('workplaces.update')
                // Delegaciones editan sedes de la hija sin workplaces.* (sesión = matriz).
                || $user->can('createDelegation')
                || $user->can('updateDelegation')
            ),
            403,
        );
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
