<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreatePermission;
use App\Domains\Admin\Actions\DeletePermission;
use App\Domains\Admin\Http\Requests\PermissionTableRequest;
use App\Domains\Admin\Http\Requests\StorePermissionRequest;
use App\Domains\Admin\Http\Resources\PermissionResource;
use App\Domains\Admin\Models\Functionality;
use App\Http\Controllers\Controller;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Permission::class);

        return Inertia::render('Admin/Permissions/Index', [
            'functionalities' => Functionality::query()
                ->orderBy('slug')
                ->get(['id', 'slug', 'label'])
                ->map(fn (Functionality $functionality): array => [
                    'id' => $functionality->id,
                    'slug' => $functionality->slug,
                    'label' => (string) $functionality->label,
                ])
                ->values()
                ->all(),
        ]);
    }

    public function table(PermissionTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', Permission::class);

        return $table->for(Permission::query()->where('guard_name', 'web'))
            ->searchable(['name'])
            ->request($request)
            ->through(PermissionResource::class)
            ->json();
    }

    public function store(StorePermissionRequest $request, CreatePermission $action): RedirectResponse
    {
        $action->handle($request->validated());

        return back()->with('success', __('permiso_creado'));
    }

    public function destroy(Permission $permission, DeletePermission $action): RedirectResponse
    {
        $this->authorize('delete', $permission);
        $action->handle($permission);

        return back()->with('success', __('permiso_eliminado'));
    }
}
