<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use App\Domains\Partners\Models\Company;
use Illuminate\Support\Facades\DB;

class AuthorizeBasicModulesForCompany
{
    public function __construct(
        private readonly AuthorizeModuleForCompany $authorizeModuleForCompany,
        private readonly SyncManagerPermissionsForCompany $syncManagerPermissionsForCompany,
    ) {}

    public function handle(Company $company): void
    {
        if (! $company->isActive()) {
            return;
        }

        DB::transaction(function () use ($company): void {
            Module::query()
                ->where('status', ModuleStatus::Active)
                ->where('level', ModuleLevel::Basic)
                ->each(function (Module $module) use ($company): void {
                    $this->authorizeModuleForCompany->handle($company, $module);
                });

            $this->syncManagerPermissionsForCompany->handle($company);
        });
    }
}
