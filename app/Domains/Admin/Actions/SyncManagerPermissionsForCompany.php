<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\CompanyModuleAuthorization;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Models\Company;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Resync idempotente: manager = comodines + permisos de Functionalities de Modules
 * Active ∩ autorizados ∩ level ∈ {Basic, Optional}. Excluye AdminOnly.
 * AND: el manager necesita `{module}.access` además de los viewAny.
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

            $modules = Module::query()
                ->whereIn('id', $authorizedModuleIds)
                ->where('status', ModuleStatus::Active)
                ->whereIn('level', [ModuleLevel::Basic, ModuleLevel::Optional])
                ->get(['id', 'slug']);

            if ($modules->isEmpty()) {
                $manager->syncPermissions([]);

                return;
            }

            $permissionNames = [];

            foreach ($modules as $module) {
                $permissionNames[] = $module->slug.'.access';
            }

            $functionalitySlugs = Functionality::query()
                ->whereIn('module_id', $modules->pluck('id'))
                ->pluck('slug');

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
