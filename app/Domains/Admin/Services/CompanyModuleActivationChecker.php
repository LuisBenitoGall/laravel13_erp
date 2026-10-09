<?php

namespace App\Domains\Admin\Services;

use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\CompanyModule;
use App\Domains\Admin\Models\CompanyModuleAuthorization;
use App\Domains\Admin\Models\Module;
use App\Support\Modules\ModuleActivationChecker;
use App\Support\Tenancy\CurrentCompany;

/**
 * Triple condición R-AUT-05/08: Active global + autorizado + encendido.
 */
final class CompanyModuleActivationChecker implements ModuleActivationChecker
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
    ) {}

    public function isActive(string $module): bool
    {
        $companyId = $this->currentCompany->id();

        if ($companyId === null) {
            return false;
        }

        $catalog = Module::query()->where('slug', $module)->first();

        if ($catalog === null || $catalog->status !== ModuleStatus::Active) {
            return false;
        }

        $authorized = CompanyModuleAuthorization::query()->withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('module_id', $catalog->id)
            ->exists();

        if (! $authorized) {
            return false;
        }

        return CompanyModule::query()->withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('module_id', $catalog->id)
            ->exists();
    }
}
