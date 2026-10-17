<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Models\Company;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\PermissionRegistrar;

/**
 * Materializa plantillas universales (`company_id` NULL, `universal`) como roles locales.
 * Manager: permisos vía SyncManagerPermissionsForCompany (no copia plantilla).
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

                if ($template->name !== Role::MANAGER) {
                    $local->syncPermissions($template->permissions);
                }

                $created[$template->name] = $local;
            }

            $created[Role::MANAGER] ??= Role::findOrCreate(Role::MANAGER, 'web');
            $created[Role::GUEST] ??= Role::findOrCreate(Role::GUEST, 'web');

            return $created;
        } finally {
            setPermissionsTeamId($previousTeamId);
        }
    }
}
