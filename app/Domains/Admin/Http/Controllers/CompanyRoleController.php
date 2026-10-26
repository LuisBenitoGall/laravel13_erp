<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateCompanyRole;
use App\Domains\Admin\Actions\DeleteCompanyRole;
use App\Domains\Admin\Actions\UpdateCompanyRole;
use App\Domains\Admin\Http\Requests\CompanyRoleTableRequest;
use App\Domains\Admin\Http\Requests\StoreCompanyRoleRequest;
use App\Domains\Admin\Http\Requests\UpdateCompanyRoleRequest;
use App\Domains\Admin\Http\Resources\CompanyRoleResource;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Support\CompanyRolePermissionCatalog;
use App\Domains\Partners\Models\Company;
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

        return $table->for(Role::query()->forCompany($company->id))
            ->searchable(['name', 'description'])
            ->request($request)
            ->through(CompanyRoleResource::class)
            ->json();
    }

    public function create(CompanyRolePermissionCatalog $catalog): Response
    {
        $this->authorize('createCompanyRole');
        $company = $this->currentCompany();

        return Inertia::render('Admin/CompanyRoles/Create', [
            'matrix' => $catalog->forCompany($company),
        ]);
    }

    public function store(StoreCompanyRoleRequest $request, CreateCompanyRole $action): RedirectResponse
    {
        $this->authorize('createCompanyRole');
        $company = $this->currentCompany();

        $role = $action->handle($company, $request->validated());

        return to_route('company.roles.edit', $role)
            ->with('success', __('rol_creado'));
    }

    public function edit(Role $companyRole, CompanyRolePermissionCatalog $catalog): Response
    {
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
        $this->authorize('updateCompanyRole', $companyRole);
        $company = $this->currentCompany();

        $action->handle($company, $companyRole, $request->validated());

        return back()->with('success', __('rol_actualizado'));
    }

    public function destroy(Role $companyRole, DeleteCompanyRole $action): RedirectResponse
    {
        $this->authorize('deleteCompanyRole', $companyRole);
        $company = $this->currentCompany();

        $action->handle($company, $companyRole);

        return to_route('company.roles.index')
            ->with('success', __('rol_eliminado'));
    }

    private function currentCompany(): Company
    {
        $companyId = app(CurrentCompany::class)->id();
        abort_if($companyId === null, 404);

        return Company::query()->findOrFail($companyId);
    }
}
