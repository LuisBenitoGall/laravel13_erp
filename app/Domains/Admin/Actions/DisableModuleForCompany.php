<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\CompanyModule;
use App\Domains\Admin\Models\Module;
use App\Domains\Partners\Models\Company;
use Illuminate\Support\Facades\DB;

class DisableModuleForCompany
{
    public function handle(Company $company, Module $module): void
    {
        DB::transaction(function () use ($company, $module): void {
            CompanyModule::query()->withoutGlobalScope('company')
                ->where('company_id', $company->id)
                ->where('module_id', $module->id)
                ->delete();
        });
    }
}
