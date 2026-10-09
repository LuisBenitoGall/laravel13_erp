<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use Illuminate\Support\Facades\DB;

/** Al crear/activar un Module Basic, autoriza companies activas existentes. */
class AuthorizeModuleForActiveCompanies
{
    public function __construct(
        private readonly AuthorizeModuleForCompany $authorizeModuleForCompany,
    ) {}

    public function handle(Module $module): void
    {
        if ($module->status !== ModuleStatus::Active || $module->level !== ModuleLevel::Basic) {
            return;
        }

        DB::transaction(function () use ($module): void {
            Company::query()
                ->where('status', CompanyStatus::Active)
                ->each(function (Company $company) use ($module): void {
                    $this->authorizeModuleForCompany->handle($company, $module);
                });
        });
    }
}
