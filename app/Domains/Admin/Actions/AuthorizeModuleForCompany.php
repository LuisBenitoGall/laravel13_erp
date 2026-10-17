<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\CompanyModuleAuthorization;
use App\Domains\Admin\Models\Module;
use App\Domains\Partners\Models\Company;
use Illuminate\Support\Facades\DB;

class AuthorizeModuleForCompany
{
    public function __construct(
        private readonly SyncManagerPermissionsForCompany $syncManagerPermissionsForCompany,
    ) {}

    public function handle(Company $company, Module $module): CompanyModuleAuthorization
    {
        return DB::transaction(function () use ($company, $module) {
            /** @var CompanyModuleAuthorization|null $existing */
            $existing = CompanyModuleAuthorization::query()
                ->withoutGlobalScope('company')
                ->withTrashed()
                ->where('company_id', $company->id)
                ->where('module_id', $module->id)
                ->first();

            if ($existing !== null) {
                if ($existing->trashed()) {
                    $existing->restore();
                }

                $this->syncManagerPermissionsForCompany->handle($company);

                return $existing;
            }

            $authorization = CompanyModuleAuthorization::query()->withoutGlobalScope('company')->create([
                'company_id' => $company->id,
                'module_id' => $module->id,
            ]);

            $this->syncManagerPermissionsForCompany->handle($company);

            return $authorization;
        });
    }
}
