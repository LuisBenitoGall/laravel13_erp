<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Models\Company;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class UpdateCompany
{
    public function __construct(
        private readonly AuthorizeBasicModulesForCompany $authorizeBasicModulesForCompany,
    ) {}

    /** @param  array<string, mixed>  $data */
    public function handle(Company $company, array $data): Company
    {
        return DB::transaction(function () use ($company, $data) {
            $data['slug'] = UniqueSlug::from(
                (string) ($data['name'] ?? ''),
                fn (string $slug): bool => Company::withTrashed()
                    ->where('slug', $slug)
                    ->whereKeyNot($company->id)
                    ->exists(),
            );

            $wasActive = $company->status === CompanyStatus::Active;
            $company->update($data);
            $company->refresh();

            if (! $wasActive && $company->status === CompanyStatus::Active) {
                $this->authorizeBasicModulesForCompany->handle($company);
            }

            return $company;
        });
    }
}
