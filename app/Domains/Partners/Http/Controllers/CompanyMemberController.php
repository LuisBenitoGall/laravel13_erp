<?php

namespace App\Domains\Partners\Http\Controllers;

use App\Domains\Admin\Actions\AssignCompanyRole;
use App\Domains\Admin\Actions\EnsureCompanyDefaultRoles;
use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Http\Requests\AssignCompanyRoleRequest;
use App\Domains\Partners\Models\Company;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CompanyMemberController extends Controller
{
    public function index(Company $company): Response
    {
        $user = request()->user();
        abort_unless($user !== null && $company->isLinkedTo($user), 403);
        abort_unless($user->can('company-users.viewAny'), 403);

        app(EnsureCompanyDefaultRoles::class)->handle($company);

        $roles = Role::query()
            ->forCompany($company->id)
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'name' => $role->name,
                'label' => $role->description,
            ])
            ->values()
            ->all();

        $previous = getPermissionsTeamId();
        setPermissionsTeamId($company->id);

        try {
            $teamKey = (string) config('permission.column_names.team_foreign_key');
            $rolesTable = (string) config('permission.table_names.roles');

            $users = $company->users()
                ->orderBy('users.name')
                ->with(['roles' => fn ($q) => $q->where("{$rolesTable}.{$teamKey}", $company->id)])
                ->get();

            /** @var list<array{id: int, name: string, email: string, role: string|null}> $members */
            $members = [];

            foreach ($users as $row) {
                /** @var User $row */
                $roleName = $row->roles->pluck('name')->first();

                $members[] = [
                    'id' => $row->id,
                    'name' => $row->name,
                    'email' => $row->email,
                    'role' => is_string($roleName) ? $roleName : null,
                ];
            }
        } finally {
            setPermissionsTeamId($previous);
        }

        return Inertia::render('Partners/Companies/Members', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'slug' => $company->slug,
            ],
            'members' => $members,
            'roles' => $roles,
        ]);
    }

    public function updateRole(
        AssignCompanyRoleRequest $request,
        Company $company,
        User $user,
        AssignCompanyRole $action,
    ): RedirectResponse {
        $action->handle($user, $company, $request->validated('role'));

        return back()->with('success', __('rol_empresa_asignado'));
    }
}
