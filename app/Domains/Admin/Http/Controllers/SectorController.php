<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateSector;
use App\Domains\Admin\Actions\DeleteSector;
use App\Domains\Admin\Actions\ToggleSectorStatus;
use App\Domains\Admin\Actions\UpdateSector;
use App\Domains\Admin\Enums\SectorStatus;
use App\Domains\Admin\Http\Requests\SectorTableRequest;
use App\Domains\Admin\Http\Requests\StoreSectorRequest;
use App\Domains\Admin\Http\Requests\ToggleSectorStatusRequest;
use App\Domains\Admin\Http\Requests\UpdateSectorRequest;
use App\Domains\Admin\Http\Resources\SectorResource;
use App\Domains\Admin\Models\Sector;
use App\Http\Controllers\Controller;
use App\Support\I18n\LocaleResolver;
use App\Support\Table\ActiveFirstOrder;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class SectorController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Sector::class);

        return Inertia::render('Admin/Sectors/Index');
    }

    public function table(
        SectorTableRequest $request,
        TableBuilder $table,
        LocaleResolver $locales,
    ): JsonResponse {
        $this->authorize('viewAny', Sector::class);

        $query = Sector::query();

        if ($request->sorts() === []) {
            $locale = $locales->resolve($request);
            ActiveFirstOrder::apply($query, SectorStatus::Active);

            if (DB::getDriverName() === 'sqlite') {
                $query->orderByRaw("json_extract(name, '$.{$locale}')");
            } else {
                $query->orderByRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(`name`, '$.{$locale}'))",
                );
            }
        }

        return $table->for($query)
            ->searchable(['name', 'slug'])
            ->request($request)
            ->through(SectorResource::class)
            ->json();
    }

    public function store(StoreSectorRequest $request, CreateSector $action): RedirectResponse
    {
        $action->handle($request->validated());

        return back()->with('success', __('sector_creado'));
    }

    public function update(
        UpdateSectorRequest $request,
        Sector $sector,
        UpdateSector $action,
    ): RedirectResponse {
        $action->handle($sector, $request->validated());

        return back()->with('success', __('sector_actualizado'));
    }

    public function updateStatus(
        ToggleSectorStatusRequest $request,
        Sector $sector,
        ToggleSectorStatus $action,
    ): JsonResponse {
        $status = $request->validated('status');

        $sector = $action->handle(
            $sector,
            is_string($status) ? SectorStatus::from($status) : null,
        );

        return response()->json([
            'status' => $sector->status->value,
        ]);
    }

    public function destroy(Sector $sector, DeleteSector $action): RedirectResponse
    {
        $this->authorize('delete', $sector);
        $action->handle($sector);

        return to_route('admin.sectors.index')
            ->with('success', __('sector_eliminado'));
    }
}
