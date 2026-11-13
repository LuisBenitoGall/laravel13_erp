<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Models\Company;
use App\Models\User;
use App\Support\Strings\UniqueSlug;
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
            $data['slug'] = UniqueSlug::from(
                (string) ($data['name'] ?? ''),
                fn (string $slug): bool => Company::withTrashed()->where('slug', $slug)->exists(),
            );

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
