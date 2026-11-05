<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Asegura o revoca `{module}.access` según existan permisos de Functionalities del Module.
 */
class SyncPlatformRoleModuleAccess
{
    public function handle(Role $role, string $moduleSlug): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $module = Module::query()->where('slug', $moduleSlug)->first();

        if ($module === null) {
            return;
        }

        $functionalitySlugs = Functionality::query()
            ->where('module_id', $module->id)
            ->pluck('slug');

        $role->unsetRelation('permissions');
        $role->load('permissions');

        $hasFunctionalityPermission = $role->permissions->contains(function (Permission $permission) use ($functionalitySlugs): bool {
            $parts = explode('.', $permission->name, 2);
            if (count($parts) !== 2) {
                return false;
            }

            [$slug, $action] = $parts;

            return $action !== 'access' && $functionalitySlugs->contains($slug);
        });

        $accessName = $moduleSlug.'.access';
        Permission::findOrCreate($accessName);

        if ($hasFunctionalityPermission) {
            if (! $role->hasPermissionTo($accessName)) {
                $role->givePermissionTo($accessName);
            }
        } elseif ($role->hasPermissionTo($accessName)) {
            $role->revokePermissionTo($accessName);
        }

        $role->unsetRelation('permissions');
    }
}
