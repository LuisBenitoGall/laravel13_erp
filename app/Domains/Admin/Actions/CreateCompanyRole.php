<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Support\CompanyRolePermissionCatalog;
use App\Domains\Partners\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class CreateCompanyRole
{
    public function __construct(
        private readonly CompanyRolePermissionCatalog $catalog,
        private readonly SyncCompanyRolePermissions $syncPermissions,
    ) {}

    /**
     * @param  array{name: string, description?: string|null, permissions?: list<string>}  $data
     */
    public function handle(Company $company, array $data): Role
    {
        $name = $data['name'];

        if (in_array($name, [Role::SUPER_ADMIN, Role::MANAGER, Role::GUEST], true)) {
            throw ValidationException::withMessages([
                'name' => __('rol_empresa_nombre_reservado'),
            ]);
        }

        return DB::transaction(function () use ($company, $data, $name) {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
            $previousTeamId = getPermissionsTeamId();
            setPermissionsTeamId($company->id);

            try {
                if (Role::query()->forCompany($company->id)->where('name', $name)->exists()) {
                    throw ValidationException::withMessages([
                        'name' => __('rol_empresa_nombre_duplicado'),
                    ]);
                }

                /** @var Role $role */
                $role = Role::create([
                    'name' => $name,
                    'guard_name' => 'web',
                    'universal' => false,
                    'description' => $data['description'] ?? null,
                ]);

                $permissions = $this->catalog->filterPermissionNames(
                    $company,
                    $data['permissions'] ?? [],
                );

                $this->syncPermissions->handle($role, $permissions);

                $fresh = $role->fresh(['permissions']);

                return $fresh instanceof Role ? $fresh : $role;
            } finally {
                setPermissionsTeamId($previousTeamId);
            }
        });
    }
}
