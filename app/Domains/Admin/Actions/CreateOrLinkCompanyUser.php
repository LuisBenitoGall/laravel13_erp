<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class CreateOrLinkCompanyUser
{
    /**
     * @param  array{
     *     name: string,
     *     surname?: string|null,
     *     email: string,
     *     status: bool,
     *     is_public: bool,
     *     send_credentials: bool,
     *     role: string
     * }  $data
     * @return array{user: User, flash_key: string}
     */
    public function handle(Company $company, array $data, User $actor): array
    {
        $this->assertCompanyIsCurrent($company);

        return DB::transaction(function () use ($company, $data, $actor): array {
            $email = (string) $data['email'];
            $existing = User::withTrashed()->where('email', $email)->first();

            if ($existing !== null) {
                return $this->linkExisting($company, $existing, $data, $actor);
            }

            return $this->createNew($company, $data, $actor);
        });
    }

    private function assertCompanyIsCurrent(Company $company): void
    {
        $currentId = app(CurrentCompany::class)->id();

        if ($currentId === null || (int) $currentId !== (int) $company->id) {
            abort(403);
        }
    }

    /**
     * @param  array{
     *     name: string,
     *     surname?: string|null,
     *     email: string,
     *     status: bool,
     *     is_public: bool,
     *     send_credentials: bool,
     *     role: string
     * }  $data
     * @return array{user: User, flash_key: string}
     */
    private function createNew(Company $company, array $data, User $actor): array
    {
        $password = null;
        $flashKey = 'usuario_creado';

        if ($data['send_credentials']) {
            $password = Str::password(16);
            $flashKey = 'credenciales_usuario_mascara';
        }

        $user = User::query()->create([
            'name' => $data['name'],
            'surname' => $data['surname'] ?? null,
            'email' => $data['email'],
            'password' => $password,
            'is_admin' => true,
            'is_public' => $data['is_public'],
            'status' => $data['status'],
        ]);

        $company->users()->attach($user->id);
        $this->assignSelectedRole($user, $company, $data['role'], $actor);

        return ['user' => $user->fresh() ?? $user, 'flash_key' => $flashKey];
    }

    /**
     * @param  array{
     *     name: string,
     *     surname?: string|null,
     *     email: string,
     *     status: bool,
     *     is_public: bool,
     *     send_credentials: bool,
     *     role: string
     * }  $data
     * @return array{user: User, flash_key: string}
     */
    private function linkExisting(Company $company, User $existing, array $data, User $actor): array
    {
        if ($existing->trashed()) {
            throw ValidationException::withMessages([
                'email' => __('usuario_email_conflicto_eliminado'),
            ]);
        }

        if ($existing->companies()->where('companies.id', $company->id)->exists()) {
            throw ValidationException::withMessages([
                'email' => __('usuario_ya_vinculado_empresa'),
            ]);
        }

        $wasAdmin = (bool) $existing->is_admin;
        $hadPassword = filled($existing->getAttributes()['password'] ?? null);

        $fill = ['is_admin' => true];

        if ($this->isBlank($existing->name)) {
            $fill['name'] = $data['name'];
        }

        if ($this->isBlank($existing->surname) && array_key_exists('surname', $data)) {
            $fill['surname'] = $data['surname'];
        }

        // status / is_public son boolean cast: no hay «hueco null» que rellenar.

        $flashKey = 'vinculacion_usuario_mascara';

        if (! $wasAdmin && ! $hadPassword && $data['send_credentials']) {
            $fill['password'] = Str::password(16);
            $flashKey = 'vinculacion_y_credenciales_usuario_mascara';
        }

        $existing->forceFill($fill)->save();
        $company->users()->attach($existing->id);
        $this->assignSelectedRole($existing, $company, $data['role'], $actor);

        return ['user' => $existing->fresh() ?? $existing, 'flash_key' => $flashKey];
    }

    private function assignSelectedRole(User $user, Company $company, string $roleName, User $actor): void
    {
        if ($roleName === Role::SUPER_ADMIN) {
            if (! $this->actorIsSuperAdmin($actor)) {
                throw ValidationException::withMessages([
                    'role' => __('rol_super_admin_solo_sa'),
                ]);
            }

            $previousTeamId = getPermissionsTeamId();
            setPermissionsTeamId(null);
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            try {
                $user->unsetRelation('roles');
                $user->unsetRelation('permissions');

                if (! $user->hasRole(Role::SUPER_ADMIN)) {
                    $user->assignRole(Role::SUPER_ADMIN);
                }

                $user->forceFill(['is_admin' => true])->save();
            } finally {
                setPermissionsTeamId($previousTeamId);
                $user->unsetRelation('roles');
                $user->unsetRelation('permissions');
            }

            return;
        }

        app(AssignCompanyRole::class)->handle($user, $company, $roleName);
    }

    private function actorIsSuperAdmin(User $actor): bool
    {
        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId(null);

        try {
            $actor->unsetRelation('roles');
            $actor->unsetRelation('permissions');

            return $actor->hasRole(Role::SUPER_ADMIN);
        } finally {
            setPermissionsTeamId($previousTeamId);
            $actor->unsetRelation('roles');
            $actor->unsetRelation('permissions');
        }
    }

    private function isBlank(mixed $value): bool
    {
        return $value === null || $value === '';
    }
}
