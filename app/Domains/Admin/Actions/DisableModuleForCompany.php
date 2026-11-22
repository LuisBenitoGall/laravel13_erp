<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\CompanyModule;
use App\Domains\Admin\Models\Module;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DisableModuleForCompany
{
    public function handle(Company $company, Module $module): void
    {
        DB::transaction(function () use ($company, $module): void {
            if ($module->level !== ModuleLevel::Optional) {
                throw new RuntimeException('Only Optional modules can be disabled for a company.');
            }

            CompanyModule::query()->withoutGlobalScope('company')
                ->where('company_id', $company->id)
                ->where('module_id', $module->id)
                ->delete();
        });
    }
}
