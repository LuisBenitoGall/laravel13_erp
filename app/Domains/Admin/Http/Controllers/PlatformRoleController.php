<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CopyPlatformRoleToCompanies;
use App\Domains\Admin\Actions\CreatePlatformRole;
use App\Domains\Admin\Actions\DeletePlatformRole;
use App\Domains\Admin\Actions\TogglePlatformRoleFunctionality;
use App\Domains\Admin\Actions\TogglePlatformRolePermission;
use App\Domains\Admin\Actions\UpdatePlatformRole;
use App\Domains\Admin\Http\Requests\CopyPlatformRoleRequest;
use App\Domains\Admin\Http\Requests\PlatformRoleTableRequest;
use App\Domains\Admin\Http\Requests\StorePlatformRoleRequest;
use App\Domains\Admin\Http\Requests\TogglePlatformRoleFunctionalityRequest;
use App\Domains\Admin\Http\Requests\TogglePlatformRolePermissionRequest;
use App\Domains\Admin\Http\Requests\UpdatePlatformRoleRequest;
use App\Domains\Admin\Http\Resources\PlatformRoleResource;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Support\PlatformRolePermissionCatalog;
use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use App\Http\Controllers\Controller;
use App\Support\Table\TableBuilder;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return $table->for(Role::query()->platformManaged()->withCount('users'))
            ->searchable(['name', 'description'])
            ->request($request)
            ->through(PlatformRoleResource::class)
            ->json();
    }

    public function create(): Response
    {
        $this->authorize('create', Role::class);

        return Inertia::render('Admin/Roles/Create');
    }

    public function store(StorePlatformRoleRequest $request, CreatePlatformRole $action): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $role = $action->handle($request->validated());

        return to_route('admin.roles.edit', $role)
            ->with('success', __('rol_creado'));
    }

    public function edit(Request $request, Role $role, PlatformRolePermissionCatalog $catalog): Response
    {
        $this->assertPlatformManaged($role);
        $this->authorize('update', $role);

        $role->load('permissions');

        $user = $request->user();
        $currentCompanyId = app(CurrentCompany::class)->id();
        $copyCompanies = [];
        $linkedActiveCount = 0;

        if ($user !== null && ! $role->universal) {
            $linkedActive = Company::query()
                ->forUser($user)
                ->where('status', CompanyStatus::Active)
                ->orderBy('name')
                ->get(['id', 'name']);

            $linkedActiveCount = $linkedActive->count();

            $copyCompanies = $linkedActive
                ->when(
                    $currentCompanyId !== null,
                    fn ($companies) => $companies->where('id', '!=', $currentCompanyId),
                )
                ->values()
                ->map(fn (Company $company): array => [
                    'id' => $company->id,
                    'name' => $company->name,
                ])
                ->all();
        }

        // CTA solo si no universal y el usuario tiene ≥2 empresas vinculadas activas.
        $canCopy = ! $role->universal && $linkedActiveCount > 1;

        return Inertia::render('Admin/Roles/Edit', [
            'role' => PlatformRoleResource::make($role)->resolve(),
            'matrix' => $catalog->forMatrix(includeAdminOnly: ! $role->universal),
            'can_copy' => $canCopy,
            'copy_companies' => $copyCompanies,
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

    public function togglePermission(
        TogglePlatformRolePermissionRequest $request,
        Role $role,
        TogglePlatformRolePermission $action,
    ): JsonResponse {
        $this->assertPlatformManaged($role);
        $this->authorize('update', $role);

        $result = $action->handle(
            $role,
            (string) $request->validated('permission'),
            (bool) $request->validated('enabled'),
        );

        return response()->json($result);
    }

    public function toggleFunctionality(
        TogglePlatformRoleFunctionalityRequest $request,
        Role $role,
        TogglePlatformRoleFunctionality $action,
    ): JsonResponse {
        $this->assertPlatformManaged($role);
        $this->authorize('update', $role);

        $result = $action->handle(
            $role,
            (string) $request->validated('functionality'),
            (bool) $request->validated('enabled'),
        );

        return response()->json($result);
    }

    public function copy(
        CopyPlatformRoleRequest $request,
        Role $role,
        CopyPlatformRoleToCompanies $action,
    ): RedirectResponse {
        $this->assertPlatformManaged($role);
        $this->authorize('update', $role);

        /** @var list<int> $companyIds */
        $companyIds = array_map('intval', $request->validated('company_ids'));

        $action->handle($role, $request->user(), $companyIds);

        return back()->with('success', __('roles_copiados'));
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
