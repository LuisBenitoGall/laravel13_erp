<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\CompanyModule;
use App\Domains\Admin\Models\CompanyModuleAuthorization;
use App\Domains\Admin\Models\Module;
use App\Domains\Partners\Models\Company;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EnableModuleForCompany
{
    public function handle(Company $company, Module $module): CompanyModule
    {
        return DB::transaction(function () use ($company, $module) {
            if ($module->status !== ModuleStatus::Active) {
                throw new RuntimeException('Cannot enable an inactive global module.');
            }

            $authorized = CompanyModuleAuthorization::query()->withoutGlobalScope('company')
                ->where('company_id', $company->id)
                ->where('module_id', $module->id)
                ->exists();

            if (! $authorized) {
                throw new RuntimeException('Cannot enable a module that is not authorized for the company.');
            }

            /** @var CompanyModule|null $existing */
            $existing = CompanyModule::query()
                ->withoutGlobalScope('company')
                ->withTrashed()
                ->where('company_id', $company->id)
                ->where('module_id', $module->id)
                ->first();

            if ($existing !== null) {
                if ($existing->trashed()) {
                    $existing->restore();
                }

                return $existing;
            }

            return CompanyModule::query()->withoutGlobalScope('company')->create([
                'company_id' => $company->id,
                'module_id' => $module->id,
            ]);
        });
    }
}
