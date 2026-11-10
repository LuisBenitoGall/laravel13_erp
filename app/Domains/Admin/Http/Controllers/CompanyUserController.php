<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\CreateOrLinkCompanyUser;
use App\Domains\Admin\Actions\DetachCompanyUser;
use App\Domains\Admin\Actions\UpdateCompanyUserStatus;
use App\Domains\Admin\Actions\UpdateOwnPassword;
use App\Domains\Admin\Actions\UpdateUserProfile;
use App\Domains\Admin\Http\Requests\CompanyUserTableRequest;
use App\Domains\Admin\Http\Requests\StoreCompanyUserRequest;
use App\Domains\Admin\Http\Requests\UpdateCompanyUserStatusRequest;
use App\Domains\Admin\Http\Requests\UpdateOwnPasswordRequest;
use App\Domains\Admin\Http\Requests\UpdateUserProfileRequest;
use App\Domains\Admin\Http\Resources\DirectoryUserResource;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Support\UserEditPageData;
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

    public function edit(User $user, UserEditPageData $pageData): Response
    {
        $this->authorize('updateCompanyUser', $user);
        $company = $this->currentCompany();
        // Perímetro tras Gate::before: SA no ve/edita users fuera de CurrentCompany.
        abort_unless(
            $user->companies()->where('companies.id', $company->id)->exists(),
            403,
        );

        /** @var User $actor */
        $actor = request()->user();

        return Inertia::render(
            'Users/Edit',
            $pageData->build($user, $actor, 'company', $company),
        );
    }

    public function update(
        UpdateUserProfileRequest $request,
        User $user,
        UpdateUserProfile $action,
    ): RedirectResponse {
        $this->authorize('updateCompanyUser', $user);
        $company = $this->currentCompany();
        abort_unless(
            $user->companies()->where('companies.id', $company->id)->exists(),
            403,
        );

        /** @var User $actor */
        $actor = $request->user();

        $action->handle($actor, $user, $request->validated(), $company);

        return back()->with('success', __('perfil_actualizado'));
    }

    public function updatePassword(
        UpdateOwnPasswordRequest $request,
        User $user,
        UpdateOwnPassword $action,
    ): RedirectResponse {
        /** @var User $actor */
        $actor = $request->user();

        // Assert self antes de cualquier Gate (SA no bypassa cambio ajeno).
        abort_unless($actor->id === $user->id, 403);
        $this->authorize('updateCompanyUser', $user);

        $action->handle($actor, $user, $request->validated());

        return back()->with('success', __('contrasena_actualizada'));
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
