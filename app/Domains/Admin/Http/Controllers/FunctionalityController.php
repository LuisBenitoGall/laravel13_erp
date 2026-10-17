<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\DeleteFunctionality;
use App\Domains\Admin\Actions\UpdateFunctionality;
use App\Domains\Admin\Http\Requests\FunctionalityTableRequest;
use App\Domains\Admin\Http\Requests\StoreFunctionalityRequest;
use App\Domains\Admin\Http\Requests\UpdateFunctionalityRequest;
use App\Domains\Admin\Http\Resources\FunctionalityResource;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Http\Controllers\Controller;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FunctionalityController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Functionality::class);

        return Inertia::render('Admin/Functionalities/Index', [
            'modules' => Module::query()->orderBy('slug')->get(['id', 'slug', 'label']),
        ]);
    }

    public function table(FunctionalityTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', Functionality::class);

        return $table->for(Functionality::query()->with('module'))
            ->searchable(['name', 'slug'])
            ->request($request)
            ->through(FunctionalityResource::class)
            ->json();
    }

    public function create(): Response
    {
        $this->authorize('create', Functionality::class);

        return Inertia::render('Admin/Functionalities/Create', [
            'modules' => Module::query()->orderBy('slug')->get(['id', 'slug', 'label']),
        ]);
    }

    public function store(StoreFunctionalityRequest $request, CreateFunctionality $action): RedirectResponse
    {
        $functionality = $action->handle($request->validated());

        return to_route('admin.functionalities.edit', $functionality)
            ->with('success', __('funcionalidad_creada'));
    }

    public function edit(Functionality $functionality): Response
    {
        $this->authorize('update', $functionality);

        return Inertia::render('Admin/Functionalities/Edit', [
            'functionality' => FunctionalityResource::make($functionality->load('module'))->resolve(),
            'modules' => Module::query()->orderBy('slug')->get(['id', 'slug', 'label']),
        ]);
    }

    public function update(
        UpdateFunctionalityRequest $request,
        Functionality $functionality,
        UpdateFunctionality $action,
    ): RedirectResponse {
        $action->handle($functionality, $request->validated());

        return back()->with('success', __('funcionalidad_actualizada'));
    }

    public function destroy(Functionality $functionality, DeleteFunctionality $action): RedirectResponse
    {
        $this->authorize('delete', $functionality);
        $action->handle($functionality);

        return to_route('admin.functionalities.index')
            ->with('success', __('funcionalidad_eliminada'));
    }
}
