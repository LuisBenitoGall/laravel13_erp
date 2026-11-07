<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateOrLinkCompanyUser;
use App\Domains\Admin\Actions\DetachCompanyUser;
use App\Domains\Admin\Actions\UpdateCompanyUserStatus;
use App\Domains\Admin\Http\Requests\CompanyUserTableRequest;
use App\Domains\Admin\Http\Requests\StoreCompanyUserRequest;
use App\Domains\Admin\Http\Requests\UpdateCompanyUserStatusRequest;
use App\Domains\Admin\Http\Resources\DirectoryUserResource;
use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Models\Company;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Table\TableBuilder;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CompanyUserController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAnyCompanyUser');

        return Inertia::render('Admin/CompanyUsers/Index');
    }

    public function table(CompanyUserTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAnyCompanyUser');
        $company = $this->currentCompany();

        return $table->for(
            User::query()->whereHas(
                'companies',
                fn ($query) => $query->where('companies.id', $company->id),
            ),
        )
            ->searchable(['name', 'surname', 'email'])
            ->request($request)
            ->through(DirectoryUserResource::class)
            ->json();
    }

    public function create(): Response
    {
        $this->authorize('createCompanyUser');
        $company = $this->currentCompany();

        return Inertia::render('Admin/CompanyUsers/Create', [
            'roleOptions' => $this->roleOptions($company, request()->user()),
        ]);
    }

    public function store(StoreCompanyUserRequest $request, CreateOrLinkCompanyUser $action): RedirectResponse
    {
        $this->authorize('createCompanyUser');
        $company = $this->currentCompany();

        /** @var User $actor */
        $actor = $request->user();

        $result = $action->handle($company, $request->validated(), $actor);

        return to_route('admin.company-users.edit', $result['user'])
            ->with('success', __($result['flash_key']));
    }

    public function edit(User $user): Response
    {
        $this->authorize('viewCompanyUser', $user);

        return Inertia::render('Admin/CompanyUsers/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'email' => $user->email,
            ],
        ]);
    }

    public function updateStatus(
        UpdateCompanyUserStatusRequest $request,
        User $user,
        UpdateCompanyUserStatus $action,
    ): JsonResponse {
        $this->authorize('updateCompanyUser', $user);
        $company = $this->currentCompany();

        $member = $action->handle(
            $company,
            $user,
            (bool) $request->validated('status'),
        );

        return response()->json([
            'status' => (bool) $member->status,
        ]);
    }

    public function destroy(User $user, DetachCompanyUser $action): RedirectResponse
    {
        $this->authorize('deleteCompanyUser', $user);
        $company = $this->currentCompany();

        $action->handle($company, $user);

        return back()->with('success', __('usuario_desvinculado_empresa'));
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function roleOptions(Company $company, ?User $actor): array
    {
        $names = Role::query()
            ->forCompany($company->id)
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId(null);

        try {
            $universals = Role::query()
                ->universalTemplates()
                ->orderBy('name')
                ->pluck('name')
                ->all();

            $actorIsSa = false;
            if ($actor !== null) {
                $actor->unsetRelation('roles');
                $actor->unsetRelation('permissions');
                $actorIsSa = $actor->hasRole(Role::SUPER_ADMIN);
            }
        } finally {
            setPermissionsTeamId($previousTeamId);
            if ($actor !== null) {
                $actor->unsetRelation('roles');
                $actor->unsetRelation('permissions');
            }
        }

        $names = array_values(array_unique([...$names, ...$universals]));

        if ($actorIsSa) {
            $names[] = Role::SUPER_ADMIN;
        }

        sort($names);

        return array_map(
            fn (string $name): array => ['value' => $name, 'label' => $name],
            $names,
        );
    }

    private function currentCompany(): Company
    {
        $companyId = app(CurrentCompany::class)->id();
        abort_if($companyId === null, 404);

        return Company::query()->findOrFail($companyId);
    }
}
