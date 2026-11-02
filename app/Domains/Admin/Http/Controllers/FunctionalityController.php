<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\DeleteFunctionality;
use App\Domains\Admin\Actions\UpdateFunctionality;
use App\Domains\Admin\Http\Requests\FunctionalityTableRequest;
use App\Domains\Admin\Http\Requests\StoreFunctionalityRequest;
use App\Domains\Admin\Http\Requests\ToggleFunctionalityStatusRequest;
use App\Domains\Admin\Http\Requests\UpdateFunctionalityRequest;
use App\Domains\Admin\Http\Resources\FunctionalityResource;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Http\Controllers\Controller;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class FunctionalityController extends Controller
{
    /** Table filtrado por módulo (Module Edit → pestaña Funcionalidades). */
    public function moduleTable(
        FunctionalityTableRequest $request,
        Module $module,
        TableBuilder $table,
    ): JsonResponse {
        $this->authorize('viewAny', Functionality::class);

        return $table->for(
            Functionality::query()->where('module_id', $module->id)->with('module'),
        )
            ->searchable(['name', 'slug'])
            ->request($request)
            ->through(FunctionalityResource::class)
            ->json();
    }

    /** Alta anidada desde Module Edit (modal); vuelve al tab Funcionalidades. */
    public function moduleStore(
        StoreFunctionalityRequest $request,
        Module $module,
        CreateFunctionality $action,
    ): RedirectResponse {
        $action->handle($request->validated());

        return to_route('admin.modules.edit', [
            'module' => $module,
            'tab' => 'functionalities',
        ])->with('success', __('funcionalidad_creada'));
    }

    public function update(
        UpdateFunctionalityRequest $request,
        Functionality $functionality,
        UpdateFunctionality $action,
    ): RedirectResponse {
        $action->handle($functionality, $request->validated());

        return to_route('admin.modules.edit', [
            'module' => $functionality->module_id,
            'tab' => 'functionalities',
        ])->with('success', __('funcionalidad_actualizada'));
    }

    /** Toggle activo/inactivo desde Acciones del ServerTable (JSON, sin recarga). */
    public function updateStatus(
        ToggleFunctionalityStatusRequest $request,
        Functionality $functionality,
        UpdateFunctionality $action,
    ): JsonResponse {
        $functionality = $action->handle($functionality, $request->validated());

        return response()->json([
            'status' => $functionality->status->value,
        ]);
    }

    public function destroy(Functionality $functionality, DeleteFunctionality $action): RedirectResponse
    {
        $this->authorize('delete', $functionality);
        $moduleId = $functionality->module_id;
        $action->handle($functionality);

        return to_route('admin.modules.edit', [
            'module' => $moduleId,
            'tab' => 'functionalities',
        ])->with('success', __('funcionalidad_eliminada'));
    }
}
