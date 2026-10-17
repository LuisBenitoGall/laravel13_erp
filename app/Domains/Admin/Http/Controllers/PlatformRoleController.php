<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreatePlatformRole;
use App\Domains\Admin\Actions\DeletePlatformRole;
use App\Domains\Admin\Actions\UpdatePlatformRole;
use App\Domains\Admin\Http\Requests\PlatformRoleTableRequest;
use App\Domains\Admin\Http\Requests\StorePlatformRoleRequest;
use App\Domains\Admin\Http\Requests\UpdatePlatformRoleRequest;
use App\Domains\Admin\Http\Resources\PlatformRoleResource;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Support\PlatformRolePermissionCatalog;
use App\Http\Controllers\Controller;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PlatformRoleController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Role::class);

        return Inertia::render('Admin/Roles/Index');
    }

    public function table(PlatformRoleTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        return $table->for(Role::query()->platformManaged())
            ->searchable(['name', 'description'])
            ->request($request)
            ->through(PlatformRoleResource::class)
            ->json();
    }

    public function create(PlatformRolePermissionCatalog $catalog): Response
    {
        $this->authorize('create', Role::class);

        return Inertia::render('Admin/Roles/Create', [
            'matrix' => $catalog->forMatrix(includeAdminOnly: true),
            'matrix_universal' => $catalog->forMatrix(includeAdminOnly: false),
        ]);
    }

    public function store(StorePlatformRoleRequest $request, CreatePlatformRole $action): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $role = $action->handle($request->validated());

        return to_route('admin.roles.edit', $role)
            ->with('success', __('rol_creado'));
    }

    public function edit(Role $role, PlatformRolePermissionCatalog $catalog): Response
    {
        $this->assertPlatformManaged($role);
        $this->authorize('update', $role);

        $role->load('permissions');

        return Inertia::render('Admin/Roles/Edit', [
            'role' => PlatformRoleResource::make($role)->resolve(),
            'matrix' => $catalog->forMatrix(includeAdminOnly: ! $role->universal),
            'matrix_universal' => $catalog->forMatrix(includeAdminOnly: false),
        ]);
    }

    public function update(
        UpdatePlatformRoleRequest $request,
        Role $role,
        UpdatePlatformRole $action,
    ): RedirectResponse {
        $this->assertPlatformManaged($role);
        $this->authorize('update', $role);

        $action->handle($role, $request->validated());

        return back()->with('success', __('rol_actualizado'));
    }

    public function destroy(Role $role, DeletePlatformRole $action): RedirectResponse
    {
        $this->assertPlatformManaged($role);
        $this->authorize('delete', $role);

        $action->handle($role);

        return to_route('admin.roles.index')
            ->with('success', __('rol_eliminado'));
    }

    private function assertPlatformManaged(Role $role): void
    {
        abort_unless(
            $role->getAttribute(config('permission.column_names.team_foreign_key')) === null
                && ! $role->isSuperAdmin(),
            404,
        );
    }
}
