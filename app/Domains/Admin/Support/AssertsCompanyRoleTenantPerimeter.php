<?php

namespace App\Domains\Admin\Support;

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Role;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Validation\ValidationException;

/**
 * Refuerzo de perímetro en Actions tenant (independiente de Gate::before).
 */
trait AssertsCompanyRoleTenantPerimeter
{
    protected function assertCompanyIsCurrent(Company $company): void
    {
        $currentId = app(CurrentCompany::class)->id();

        if ($currentId === null || (int) $currentId !== (int) $company->id) {
            throw ValidationException::withMessages([
                'role' => __('rol_empresa_fuera_de_tenant'),
            ]);
        }
    }

    protected function assertRoleBelongsToCompany(Role $role, Company $company): void
    {
        $teamKey = (string) config('permission.column_names.team_foreign_key');

        if ((int) $role->getAttribute($teamKey) !== (int) $company->id) {
            throw ValidationException::withMessages([
                'role' => __('rol_empresa_fuera_de_tenant'),
            ]);
        }
    }
}
