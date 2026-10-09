<?php

namespace App\Domains\Partners\Actions;

use App\Domains\Admin\Actions\AuthorizeBasicModulesForCompany;
use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
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
