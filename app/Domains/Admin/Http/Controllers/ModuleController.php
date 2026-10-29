<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Actions\DeleteModule;
use App\Domains\Admin\Actions\UpdateModule;
use App\Domains\Admin\Exports\ModulesExport;
use App\Domains\Admin\Http\Requests\ModuleTableRequest;
use App\Domains\Admin\Http\Requests\StoreModuleRequest;
use App\Domains\Admin\Http\Requests\ToggleModuleStatusRequest;
use App\Domains\Admin\Http\Requests\UpdateModuleRequest;
use App\Domains\Admin\Http\Resources\ModuleResource;
use App\Domains\Admin\Models\Module;
use App\Http\Controllers\Controller;
use App\Support\Table\TableBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ModuleController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Module::class);

        return Inertia::render('Admin/Modules/Index');
    }

    public function table(
        ModuleTableRequest $request,
        TableBuilder $table,
    ): JsonResponse|BinaryFileResponse|Response {
        $this->authorize('viewAny', Module::class);

        $builder = $table->for(Module::query())
            ->searchable(['name', 'slug'])
            ->request($request)
            ->through(ModuleResource::class);

        if ($request->wantsExport()) {
            $modules = [];

            foreach ($builder->forExport() as $row) {
                if ($row instanceof Module) {
                    $modules[] = $row;
                }
            }

            return match ($request->exportFormat()) {
                'xlsx' => Excel::download(new ModulesExport($modules), 'modules.xlsx'),
                'csv' => Excel::download(new ModulesExport($modules), 'modules.csv', ExcelWriter::CSV),
                'pdf' => Pdf::loadView('exports.modules-table', ['rows' => $modules])
                    ->setPaper('a4', 'landscape')
                    ->download('modules.pdf'),
                default => abort(422),
            };
        }

        return $builder->json();
    }

    public function create(): InertiaResponse
    {
        $this->authorize('create', Module::class);

        return Inertia::render('Admin/Modules/Create');
    }

    public function store(StoreModuleRequest $request, CreateModule $action): RedirectResponse
    {
        $module = $action->handle($request->validated());

        return to_route('admin.modules.edit', $module)
            ->with('success', __('modulo_creado'));
    }

    public function edit(Module $module): InertiaResponse
    {
        $this->authorize('update', $module);

        return Inertia::render('Admin/Modules/Edit', [
            'module' => ModuleResource::make($module)->resolve(),
        ]);
    }

    public function update(UpdateModuleRequest $request, Module $module, UpdateModule $action): RedirectResponse
    {
        $action->handle($module, $request->validated());

        return back()->with('success', __('modulo_actualizado'));
    }

    /** Toggle activo/inactivo desde Acciones del ServerTable (JSON, sin recarga). */
    public function updateStatus(
        ToggleModuleStatusRequest $request,
        Module $module,
        UpdateModule $action,
    ): JsonResponse {
        $module = $action->handle($module, $request->validated());

        return response()->json([
            'status' => $module->status->value,
        ]);
    }

    public function destroy(Module $module, DeleteModule $action): RedirectResponse
    {
        $this->authorize('delete', $module);
        $action->handle($module);

        return to_route('admin.modules.index')
            ->with('success', __('modulo_eliminado'));
    }
}
