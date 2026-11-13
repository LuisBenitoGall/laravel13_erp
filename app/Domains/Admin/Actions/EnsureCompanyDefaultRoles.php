<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Role;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\PermissionRegistrar;

/**
 * Materializa plantillas universales (`company_id` NULL, `universal`) como roles locales.
 * Locales siempre `universal = false`. Manager: permisos vía SyncManagerPermissionsForCompany.
 * Resto: copia snapshot de permisos de la plantilla.
 */
class EnsureCompanyDefaultRoles
{
    /**
     * @return array<string, RoleContract>
     */
    public function handle(Company $company): array
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId(null);

        try {
            $templates = Role::query()
                ->universalTemplates()
                ->with('permissions')
                ->get();
        } finally {
            setPermissionsTeamId($previousTeamId);
        }

        setPermissionsTeamId($company->id);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        try {
            $created = [];

            foreach ($templates as $template) {
                $local = Role::findOrCreate($template->name, 'web');
                $local->forceFill(['universal' => false])->save();

                if ($template->name !== Role::MANAGER) {
                    $local->syncPermissions($template->permissions);
                }

                $created[$template->name] = $local;
            }

            $created[Role::MANAGER] ??= Role::findOrCreate(Role::MANAGER, 'web');
            $created[Role::GUEST] ??= Role::findOrCreate(Role::GUEST, 'web');

            foreach ([Role::MANAGER, Role::GUEST] as $protected) {
                if (isset($created[$protected])) {
                    $created[$protected]->forceFill(['universal' => false])->save();
                }
            }

            return $created;
        } finally {
            setPermissionsTeamId($previousTeamId);
        }
    }
}
