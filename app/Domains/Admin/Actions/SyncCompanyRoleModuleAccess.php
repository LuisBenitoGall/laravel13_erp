<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Role;

/**
 * Misma semántica que plataforma: grant/revoke `{module}.access` según permisos
 * de Functionalities del Module. El caller fija `setPermissionsTeamId` del tenant.
 */
class SyncCompanyRoleModuleAccess
{
    public function __construct(
        private readonly SyncPlatformRoleModuleAccess $sync,
    ) {}

    public function handle(Role $role, string $moduleSlug): void
    {
        $this->sync->handle($role, $moduleSlug);
    }
}
