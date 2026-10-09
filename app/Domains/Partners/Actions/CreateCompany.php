<?php

namespace App\Domains\Partners\Actions;

use App\Domains\Admin\Actions\AuthorizeBasicModulesForCompany;
use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use Illuminate\Support\Facades\DB;

class CreateCompany
{
    public function __construct(
        private readonly AuthorizeBasicModulesForCompany $authorizeBasicModulesForCompany,
    ) {}

    /** @param  array<string, mixed>  $data */
    public function handle(array $data): Company
    {
        return DB::transaction(function () use ($data) {
            $company = Company::query()->create($data);

            if ($company->status === CompanyStatus::Active) {
                $this->authorizeBasicModulesForCompany->handle($company);
            }

            return $company;
        });
    }
}
