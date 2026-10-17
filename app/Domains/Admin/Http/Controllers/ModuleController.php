<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Actions\DeleteModule;
use App\Domains\Admin\Actions\UpdateModule;
use App\Domains\Admin\Http\Requests\ModuleTableRequest;
use App\Domains\Admin\Http\Requests\StoreModuleRequest;
use App\Domains\Admin\Http\Requests\UpdateModuleRequest;
use App\Domains\Admin\Http\Resources\ModuleResource;
use App\Domains\Admin\Models\Module;
use App\Http\Controllers\Controller;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ModuleController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Module::class);

        return Inertia::render('Admin/Modules/Index');
    }

    public function table(ModuleTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', Module::class);

        return $table->for(Module::query())
            ->searchable(['name', 'slug'])
            ->request($request)
            ->through(ModuleResource::class)
            ->json();
    }

    public function create(): Response
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

    public function edit(Module $module): Response
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

    public function destroy(Module $module, DeleteModule $action): RedirectResponse
    {
        $this->authorize('delete', $module);
        $action->handle($module);

        return to_route('admin.modules.index')
            ->with('success', __('modulo_eliminado'));
    }
}
