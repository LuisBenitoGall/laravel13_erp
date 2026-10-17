<?php

namespace App\Domains\Partners\Actions;

use App\Domains\Admin\Actions\AuthorizeBasicModulesForCompany;
use App\Domains\Admin\Actions\EnsureCompanyDefaultRoles;
use App\Domains\Admin\Actions\SyncManagerPermissionsForCompany;
use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class CreateCompany
{
    public function __construct(
        private readonly EnsureCompanyDefaultRoles $ensureCompanyDefaultRoles,
        private readonly AuthorizeBasicModulesForCompany $authorizeBasicModulesForCompany,
        private readonly SyncManagerPermissionsForCompany $syncManagerPermissionsForCompany,
    ) {}

    /** @param  array<string, mixed>  $data */
    public function handle(array $data, User $creator): Company
    {
        return DB::transaction(function () use ($data, $creator) {
            $company = Company::query()->create($data);

            $this->ensureCompanyDefaultRoles->handle($company);

            if (! $creator->companies()->where('companies.id', $company->id)->exists()) {
                $creator->companies()->attach($company->id);
            }

            app()[PermissionRegistrar::class]->forgetCachedPermissions();
            $previousTeamId = getPermissionsTeamId();
            setPermissionsTeamId($company->id);

            try {
                if (! $creator->hasRole('manager')) {
                    $creator->assignRole('manager');
                }
            } finally {
                setPermissionsTeamId($previousTeamId);
            }

            if ($company->status === CompanyStatus::Active) {
                $this->authorizeBasicModulesForCompany->handle($company);
            }

            $this->syncManagerPermissionsForCompany->handle($company);

            return $company->refresh();
        });
    }
}
