<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\CompanyModuleAuthorization;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Domains\Partners\Models\Company;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Resync idempotente: manager = permisos de Functionalities de Modules
 * Active ∩ autorizados ∩ level ∈ {Basic, Optional}. Excluye AdminOnly.
 */
class SyncManagerPermissionsForCompany
{
    public function handle(Company $company): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId($company->id);

        try {
            $manager = Role::findOrCreate('manager');

            $authorizedModuleIds = CompanyModuleAuthorization::query()
                ->withoutGlobalScope('company')
                ->where('company_id', $company->id)
                ->pluck('module_id');

            if ($authorizedModuleIds->isEmpty()) {
                $manager->syncPermissions([]);

                return;
            }

            $moduleIds = Module::query()
                ->whereIn('id', $authorizedModuleIds)
                ->where('status', ModuleStatus::Active)
                ->whereIn('level', [ModuleLevel::Basic, ModuleLevel::Optional])
                ->pluck('id');

            if ($moduleIds->isEmpty()) {
                $manager->syncPermissions([]);

                return;
            }

            $functionalitySlugs = Functionality::query()
                ->whereIn('module_id', $moduleIds)
                ->pluck('slug');

            $permissionNames = [];

            foreach ($functionalitySlugs as $slug) {
                foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
                    $permissionNames[] = "{$slug}.{$ability}";
                }
            }

            $permissions = Permission::query()
                ->whereIn('name', $permissionNames)
                ->get();

            $manager->syncPermissions($permissions);
        } finally {
            setPermissionsTeamId($previousTeamId);
        }
    }
}
