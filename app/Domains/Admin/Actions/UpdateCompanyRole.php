<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Support\CompanyRolePermissionCatalog;
use App\Domains\Partners\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class UpdateCompanyRole
{
    public function __construct(
        private readonly CompanyRolePermissionCatalog $catalog,
        private readonly SyncCompanyRolePermissions $syncPermissions,
    ) {}

    /**
     * @param  array{name?: string, description?: string|null, permissions?: list<string>}  $data
     */
    public function handle(Company $company, Role $role, array $data): Role
    {
        $teamKey = (string) config('permission.column_names.team_foreign_key');

        if ((int) $role->getAttribute($teamKey) !== (int) $company->id) {
            throw ValidationException::withMessages([
                'role' => __('rol_empresa_fuera_de_tenant'),
            ]);
        }

        return DB::transaction(function () use ($company, $role, $data) {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
            $previousTeamId = getPermissionsTeamId();
            setPermissionsTeamId($company->id);

            try {
                if ($role->isLocalProtected() && isset($data['name']) && $data['name'] !== $role->name) {
                    throw ValidationException::withMessages([
                        'name' => __('rol_empresa_nombre_inmutable'),
                    ]);
                }

                if (! $role->isLocalProtected() && isset($data['name']) && $data['name'] !== $role->name) {
                    if (in_array($data['name'], [Role::SUPER_ADMIN, Role::MANAGER, Role::GUEST], true)) {
                        throw ValidationException::withMessages([
                            'name' => __('rol_empresa_nombre_reservado'),
                        ]);
                    }

                    if (Role::query()
                        ->forCompany($company->id)
                        ->where('name', $data['name'])
                        ->where('id', '!=', $role->id)
                        ->exists()) {
                        throw ValidationException::withMessages([
                            'name' => __('rol_empresa_nombre_duplicado'),
                        ]);
                    }

                    $role->name = $data['name'];
                }

                if (array_key_exists('description', $data)) {
                    $role->description = $data['description'];
                }

                $role->save();

                if (array_key_exists('permissions', $data) && ! $role->isLocalManager()) {
                    $permissions = $this->catalog->filterPermissionNames($company, $data['permissions']);
                    $this->syncPermissions->handle($role, $permissions);
                }

                return $role->fresh(['permissions']) ?? $role;
            } finally {
                setPermissionsTeamId($previousTeamId);
            }
        });
    }
}
