<?php

namespace App\Domains\Partners\Http\Controllers;

use App\Domains\Admin\Actions\AssignCompanyRole;
use App\Domains\Admin\Support\CompanyRoleGate;
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
        abort_unless(
            request()->user() !== null && CompanyRoleGate::canAssign(request()->user(), $company),
            403,
        );

        $previous = getPermissionsTeamId();
        setPermissionsTeamId($company->id);

        try {
            $members = $company->users()
                ->orderBy('users.name')
                ->get()
                ->map(function (User $user) {
                    $role = null;
                    if ($user->hasRole('manager')) {
                        $role = 'manager';
                    } elseif ($user->hasRole('guest')) {
                        $role = 'guest';
                    }

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $role,
                    ];
                })
                ->values()
                ->all();
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
