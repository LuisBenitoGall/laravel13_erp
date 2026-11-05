<?php

namespace App\Domains\Admin\Services;

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\CompanyModule;
use App\Domains\Admin\Models\CompanyModuleAuthorization;
use App\Domains\Admin\Models\Module;
use App\Models\User;
use App\Support\Modules\ModuleActivationChecker;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Support\Facades\Auth;

/**
 * Triple condición R-AUT-05/08: Active global + autorizado + encendido.
 * Excepción shell: Super Admin + ModuleLevel::AdminOnly Active → true.
 */
final class CompanyModuleActivationChecker implements ModuleActivationChecker
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
    ) {}

    public function isActive(string $module): bool
    {
        $catalog = Module::query()->where('slug', $module)->first();

        if ($catalog === null || $catalog->status !== ModuleStatus::Active) {
            return false;
        }

        if ($catalog->level === ModuleLevel::AdminOnly && $this->isSuperAdmin()) {
            return true;
        }

        $companyId = $this->currentCompany->id();

        if ($companyId === null) {
            return false;
        }

        $authorized = CompanyModuleAuthorization::query()->withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('module_id', $catalog->id)
            ->exists();

        if (! $authorized) {
            return false;
        }

        $enabled = CompanyModule::query()->withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('module_id', $catalog->id)
            ->exists();

        if (! $enabled) {
            return false;
        }

        // AND: tras las tres capas, el actor no-SA necesita el comodín del Module.
        if ($this->isSuperAdmin()) {
            return true;
        }

        $user = Auth::user();

        return $user instanceof User && $user->can($module.'.access');
    }

    private function isSuperAdmin(): bool
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        $previous = getPermissionsTeamId();
        setPermissionsTeamId(null);
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        try {
            return $user->hasRole('Super Admin');
        } finally {
            setPermissionsTeamId($previous);
            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
        }
    }
}
