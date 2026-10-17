<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class SyncPlatformRolePermissions
{
    /** @param  list<string>  $permissionNames */
    public function handle(Role $role, array $permissionNames): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = Permission::query()
            ->whereIn('name', $permissionNames)
            ->get();

        $role->syncPermissions($permissions);
    }
}
