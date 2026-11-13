<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateCompanyRole;
use App\Domains\Admin\Actions\DeleteCompanyRole;
use App\Domains\Admin\Actions\ToggleCompanyRoleFunctionality;
use App\Domains\Admin\Actions\ToggleCompanyRolePermission;
use App\Domains\Admin\Actions\UpdateCompanyRole;
use App\Domains\Admin\Http\Requests\CompanyRoleTableRequest;
use App\Domains\Admin\Http\Requests\StoreCompanyRoleRequest;
use App\Domains\Admin\Http\Requests\ToggleCompanyRoleFunctionalityRequest;
use App\Domains\Admin\Http\Requests\ToggleCompanyRolePermissionRequest;
use App\Domains\Admin\Http\Requests\UpdateCompanyRoleRequest;
use App\Domains\Admin\Http\Resources\CompanyRoleResource;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Support\CompanyRolePermissionCatalog;
use App\Http\Controllers\Controller;
use App\Support\Table\TableBuilder;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CompanyRoleController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAnyCompanyRole');

        return Inertia::render('Admin/CompanyRoles/Index');
    }

    public function table(CompanyRoleTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAnyCompanyRole');
        $company = $this->currentCompany();

        return $table->for(Role::query()->forCompany($company->id)->withCount('users'))
            ->searchable(['name', 'description'])
            ->request($request)
            ->through(CompanyRoleResource::class)
            ->json();
    }

    public function create(): Response
    {
        $this->authorize('createCompanyRole');

        return Inertia::render('Admin/CompanyRoles/Create');
    }

    public function store(StoreCompanyRoleRequest $request, CreateCompanyRole $action): RedirectResponse
    {
        $this->authorize('createCompanyRole');
        $company = $this->currentCompany();

        $role = $action->handle($company, $request->validated());

        return to_route('admin.company-roles.edit', $role)
            ->with('success', __('rol_creado'));
    }

    public function edit(Role $companyRole, CompanyRolePermissionCatalog $catalog): Response
    {
        $this->assertCompanyScopedRole($companyRole);
        $this->authorize('updateCompanyRole', $companyRole);
        $company = $this->currentCompany();

        $companyRole->load('permissions');

        return Inertia::render('Admin/CompanyRoles/Edit', [
            'role' => CompanyRoleResource::make($companyRole)->resolve(),
            'matrix' => $catalog->forCompany($company),
        ]);
    }

    public function update(
        UpdateCompanyRoleRequest $request,
        Role $companyRole,
        UpdateCompanyRole $action,
    ): RedirectResponse {
        $this->assertCompanyScopedRole($companyRole);
        $this->authorize('updateCompanyRole', $companyRole);
        $company = $this->currentCompany();

        $action->handle($company, $companyRole, $request->validated());

        return back()->with('success', __('rol_actualizado'));
    }

    public function togglePermission(
        ToggleCompanyRolePermissionRequest $request,
        Role $companyRole,
        ToggleCompanyRolePermission $action,
    ): JsonResponse {
        $this->assertCompanyScopedRole($companyRole);
        $this->authorize('updateCompanyRole', $companyRole);
        $company = $this->currentCompany();

        $result = $action->handle(
            $company,
            $companyRole,
            (string) $request->validated('permission'),
            (bool) $request->validated('enabled'),
        );

        return response()->json($result);
    }

    public function toggleFunctionality(
        ToggleCompanyRoleFunctionalityRequest $request,
        Role $companyRole,
        ToggleCompanyRoleFunctionality $action,
    ): JsonResponse {
        $this->assertCompanyScopedRole($companyRole);
        $this->authorize('updateCompanyRole', $companyRole);
        $company = $this->currentCompany();

        $result = $action->handle(
            $company,
            $companyRole,
            (string) $request->validated('functionality'),
            (bool) $request->validated('enabled'),
        );

        return response()->json($result);
    }

    public function destroy(Role $companyRole, DeleteCompanyRole $action): RedirectResponse
    {
        $this->assertCompanyScopedRole($companyRole);
        $this->authorize('deleteCompanyRole', $companyRole);
        $company = $this->currentCompany();

        $action->handle($company, $companyRole);

        return to_route('admin.company-roles.index')
            ->with('success', __('rol_eliminado'));
    }

    private function currentCompany(): Company
    {
        $companyId = app(CurrentCompany::class)->id();
        abort_if($companyId === null, 404);

        return Company::query()->findOrFail($companyId);
    }

    private function assertCompanyScopedRole(Role $role): void
    {
        $companyId = app(CurrentCompany::class)->id();
        $teamKey = (string) config('permission.column_names.team_foreign_key');

        abort_unless(
            $companyId !== null
                && (int) $role->getAttribute($teamKey) === (int) $companyId,
            404,
        );

        abort_if($role->universal, 403);
    }
}
